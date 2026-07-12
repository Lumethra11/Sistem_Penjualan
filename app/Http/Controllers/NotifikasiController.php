<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\DetailTransaksi;
use App\Models\Notifikasi;
use App\Services\KMeansService; // Pastikan namespace ini sesuai dengan lokasi KMeansService Anda
use Carbon\Carbon;
use Illuminate\Http\Request;

class NotifikasiController extends Controller
{
    /**
     * HALAMAN UTAMA NOTIFIKASI OPERASIONAL
     */
    public function index(Request $request)
    {
        $now = Carbon::now();
        $stringPeriodeMingguIni = 'Minggu ' . $now->weekOfMonth . ' Bulan ' . $now->format('F Y');

        // 1. Jalankan sinkronisasi / kalkulasi live khusus untuk bengkel user ini
        $this->generateNotifikasiKeDatabase();

        // 2. Ambil data dari tabel 'notifikasi', kunci berdasarkan user_id
        $allNotifications = Notifikasi::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // 3. Grouping berdasarkan tanggal buatan untuk visual tabel rekap
        $groupedNotifications = [];
        foreach ($allNotifications as $item) {
            $tanggalIndo = Carbon::parse($item->created_at)->translatedFormat('l, d M Y');
            $groupedNotifications[$tanggalIndo][] = $item;
        }

        return view('notifikasi.index', [
            'groupedNotifications' => $groupedNotifications,
            'allNotifications' => $allNotifications,
            'stringPeriodeMingguIni' => $stringPeriodeMingguIni
        ]);
    }

    /**
     * API JSON DROPDOWN NAVBAR MELAYANG
     */
    public function getNotificationsJson()
    {
        // 1. Jalankan sinkronisasi sebelum diambil oleh navbar floating
        $this->generateNotifikasiKeDatabase();
        
        // 2. Ambil notifikasi yang belum dibaca milik user ini
        $unreadNotifications = Notifikasi::where('user_id', auth()->id())
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->get();
            
        // 3. Transformasikan format tanggal & jam agar informatif
        $formattedData = $unreadNotifications->map(function($item) {
            return [
                'id' => $item->id,
                'id_item' => $item->id_item,
                'tipe' => $item->tipe,
                'icon' => $item->icon,
                'judul' => $item->judul,
                'pesan' => $item->pesan,
                'waktu' => Carbon::parse($item->created_at)->translatedFormat('H:i') . ' WIB',
            ];
        });
        
        return response()->json([
            'status' => 'success',
            'total' => $formattedData->count(),
            'data' => $formattedData
        ]);
    }

    /**
     * MARK AS READ (TANDAI SELESAI / DIBACA)
     */
    public function markAsRead(Request $request, $id)
    {
        // Pastikan mencari berdasarkan id_item DAN user_id agar tidak memanipulasi data bengkel lain
        $notif = Notifikasi::where('user_id', auth()->id())
            ->where('id_item', $id)
            ->first();

        if ($notif) {
            $notif->update(['is_read' => true]);
            return response()->json([
                'status' => 'success', 
                'message' => 'Notifikasi berhasil ditandai telah dibaca.'
            ]);
        }
        return response()->json([
            'status' => 'error', 
            'message' => 'Notifikasi tidak ditemukan di database.'
        ], 404);
    }

    /**
     * SINKRONISASI NOTIFIKASI BERBASIS HASIL K-MEANS DARI KMEANSSERVICE
     */
    private function generateNotifikasiKeDatabase()
    {
        $user = auth()->user();
        $userId = $user->id;
        
        // Aturan multi-tenant: Jika kasir yang mengakses, jangkar data ditarik ke admin pembuatnya
        $adminId = ($user->role === 'admin') ? $user->id : $user->admin_id;
        
        $start = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $end = Carbon::now()->endOfWeek(Carbon::SUNDAY);
        $periodeKey = $start->format('Ymd'); // Token penanda kode minggu berjalan
        
        /**
         * REFAKTORISASI UTAMA: Menggunakan KMeansService Terpusat
         * Mengambil hasil kalkulasi terpusat yang sudah include normalisasi & standarisasi
         */
        $dataset = app(KMeansService::class)->calculateKMeansRealtime($start, $end);

        // Jika dataset kosong atau tidak memenuhi syarat minimum klasterisasi di service
        if (empty($dataset)) {
            return;
        }

        // Eksekusi penyimpanan data terstruktur ke dalam tabel 'notifikasi'
        foreach ($dataset as $item) {
            
            // 1. Kondisi Stok Kritis / Habis (Batas operasional aman gudang)
            if ($item['stok'] <= 5) {
                Notifikasi::updateOrCreate(
                    [
                        'user_id' => $userId,
                        'id_item' => 'stok_' . $userId . '_' . $item['kode'] . '_' . $periodeKey
                    ],
                    [
                        'tipe' => 'stok-out',
                        'icon' => 'fa-triangle-exclamation',
                        'judul' => 'Stok Kritis / Habis',
                        'pesan' => "Sisa stok produk {$item['nama']} ({$item['kode']}) tinggal {$item['stok']} item. Segera ajukan pemesanan ulang ke supplier."
                    ]
                );
            }
            
            // 2. Kondisi Hasil Cluster Laris (Rekomendasi Restock Berbasis K-Means Sentral)
            if ($item['label'] === 'Laris') {
                Notifikasi::updateOrCreate(
                    [
                        'user_id' => $userId,
                        'id_item' => 'restock_' . $userId . '_' . $item['kode'] . '_' . $periodeKey
                    ],
                    [
                        'tipe' => 'restock',
                        'icon' => 'fa-boxes-packing',
                        'judul' => 'Rekomendasi Tambah Stok',
                        'pesan' => "Produk {$item['nama']} ({$item['kode']}) terdeteksi Sangat Laku dengan total {$item['terjual']} item terjual minggu ini. Disarankan melakukan restock."
                    ]
                );
            }
            
            // 3. Kondisi Stok Mengendap / Overstock (Barang Macet di Gudang)
            if ($item['label'] === 'Kurang Laris' && $item['terjual'] === 0 && $item['stok'] >= 30) {
                Notifikasi::updateOrCreate(
                    [
                        'user_id' => $userId,
                        'id_item' => 'overstock_' . $userId . '_' . $item['kode'] . '_' . $periodeKey
                    ],
                    [
                        'tipe' => 'overstock',
                        'icon' => 'fa-layer-group',
                        'judul' => 'Stok Mengendap (Macet)',
                        'pesan' => "Stok menumpuk sebanyak {$item['stok']} item pada produk {$item['nama']} ({$item['kode']}) di gudang tanpa ada catatan penjualan minggu ini."
                    ]
                );
            }
            
            // 4. Kondisi Hasil Cluster Kurang Laris Terjual Sedikit (Low Sales Evaluation)
            if ($item['label'] === 'Kurang Laris' && $item['terjual'] > 0) {
                Notifikasi::updateOrCreate(
                    [
                        'user_id' => $userId,
                        'id_item' => 'low-sales_' . $userId . '_' . $item['kode'] . '_' . $periodeKey
                    ],
                    [
                        'tipe' => 'low-sales',
                        'icon' => 'fa-arrow-trend-down',
                        'judul' => 'Evaluasi Penjualan Rendah',
                        'pesan' => "Perputaran produk {$item['nama']} ({$item['kode']}) lambat, baru berhasil mencatatkan {$item['terjual']} penjualan item pada minggu ini."
                    ]
                );
            }
        }
    }
}