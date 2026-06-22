<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\DetailTransaksi;
use App\Models\Notifikasi; // Diubah penuh menggunakan nama Model Anda
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class NotifikasiController extends Controller
{
    /**
     * HALAMAN UTAMA NOTIFIKASI OPERASIONAL
     */
    public function index(Request $request)
    {
        $now = Carbon::now();
        $stringPeriodeMingguIni = 'Minggu ' . $now->weekOfMonth . ' Bulan ' . $now->format('F Y');

        // 1. Jalankan sinkronisasi / kalkulasi live ke database terlebih dahulu
        $this->generateNotifikasiKeDatabase();

        // 2. Ambil data dari tabel 'notifikasi', urutkan dari yang terbaru (desc)
        $allNotifications = Notifikasi::orderBy('created_at', 'desc')->paginate(10);

        // 3. Grouping berdasarkan tanggal buatan untuk visual tabel rekap
        $groupedNotifications = [];
        foreach ($allNotifications as $item) {
            // Contoh hasil format: "Senin, 22 Jun 2026"
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
        
        // 2. Ambil notifikasi yang belum dibaca (is_read = false), urutkan paling baru
        $unreadNotifications = Notifikasi::where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->get();
            
        // 3. Transformasikan format tanggal & jam agar informatif di dropdown melayang
        $formattedData = $unreadNotifications->map(function($item) {
            return [
                'id' => $item->id,
                'id_item' => $item->id_item,
                'tipe' => $item->tipe,
                'icon' => $item->icon,
                'judul' => $item->judul,
                'pesan' => $item->pesan,
                // Mengambil waktu jam dan menit yang presisi
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
        $notif = Notifikasi::where('id_item', $id)->first();
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
     * LOGIKA ANALISIS K-MEANS & INTEGRASI SIMPAN KE DATABASE
     */
    private function generateNotifikasiKeDatabase()
    {
        $start = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $end = Carbon::now()->endOfWeek(Carbon::SUNDAY);
        $periodeKey = $start->format('Ymd'); // Token penanda kode minggu berjalan
        
        $semuaBarang = Barang::where('tipe_barang', 'stok')->get();
        $dataset = [];

        foreach ($semuaBarang as $b) {
            $totalTerjual = DetailTransaksi::where('barang_id', $b->id)
                ->whereHas('transaksi', function($q) use ($start, $end) {
                    $q->where('status', 'selesai')->whereBetween('created_at', [$start, $end]);
                })->sum('jumlah');

            $dataset[] = [
                'kode' => $b->kode_barang,
                'nama' => $b->nama_barang,
                'stok' => (int)$b->stok,
                'terjual' => (int)$totalTerjual,
                'label' => 'Sedang'
            ];
        }

        // Jalankan Algoritma K-Means jika ragam suku cadang memadai (K=3)
        if (count($dataset) >= 3) {
            $stokVals = array_column($dataset, 'stok');
            $jualVals = array_column($dataset, 'terjual');
            $centroids = [
                'Laris' => ['stok' => min($stokVals), 'terjual' => max($jualVals)],
                'Sedang' => ['stok' => array_sum($stokVals)/count($stokVals), 'terjual' => array_sum($jualVals)/count($jualVals)],
                'Kurang Laris' => ['stok' => max($stokVals), 'terjual' => min($jualVals)]
            ];

            for ($i = 0; $i < 10; $i++) {
                $clusterBaru = ['Laris' => [], 'Sedang' => [], 'Kurang Laris' => []];
                foreach ($dataset as $idx => $data) {
                    $jarakLaris  = sqrt(pow($data['stok'] - $centroids['Laris']['stok'], 2) + pow($data['terjual'] - $centroids['Laris']['terjual'], 2));
                    $jarakSedang = sqrt(pow($data['stok'] - $centroids['Sedang']['stok'], 2) + pow($data['terjual'] - $centroids['Sedang']['terjual'], 2));
                    $jarakKurang = sqrt(pow($data['stok'] - $centroids['Kurang Laris']['stok'], 2) + pow($data['terjual'] - $centroids['Kurang Laris']['terjual'], 2));
                    $minJarak = min($jarakLaris, $jarakSedang, $jarakKurang);
                    
                    $label = ($minJarak == $jarakLaris) ? 'Laris' : (($minJarak == $jarakSedang) ? 'Sedang' : 'Kurang Laris');
                    if ($data['terjual'] === 0) { $label = 'Kurang Laris'; }

                    $dataset[$idx]['label'] = $label;
                    $clusterBaru[$label][] = $data;
                }
                $centroidsLama = $centroids;
                foreach (['Laris', 'Sedang', 'Kurang Laris'] as $l) {
                    if (count($clusterBaru[$l]) > 0) {
                        $centroids[$l]['stok'] = array_sum(array_column($clusterBaru[$l], 'stok')) / count($clusterBaru[$l]);
                        $centroids[$l]['terjual'] = array_sum(array_column($clusterBaru[$l], 'terjual')) / count($clusterBaru[$l]);
                    }
                }
                if ($centroidsLama == $centroids) { break; }
            }
        }

        // Eksekusi penyimpanan data terstruktur ke dalam tabel 'notifikasi'
        foreach ($dataset as $item) {
            // 1. Kondisi Stok Kritis / Habis (Top-up Alert)
            if ($item['stok'] <= 5) {
                Notifikasi::updateOrCreate(
                    ['id_item' => 'stok_' . $item['kode'] . '_' . $periodeKey],
                    [
                        'tipe' => 'stok-out',
                        'icon' => 'fa-triangle-exclamation',
                        'judul' => 'Stok Kritis / Habis',
                        'pesan' => "Sisa stok produk {$item['nama']} ({$item['kode']}) tinggal {$item['stok']} item. Segera ajukan pemesanan ulang ke supplier."
                    ]
                );
            }
            
            // 2. Kondisi Hasil Cluster Laris (Rekomendasi Restock Berbasis K-Means)
            if ($item['label'] === 'Laris') {
                Notifikasi::updateOrCreate(
                    ['id_item' => 'restock_' . $item['kode'] . '_' . $periodeKey],
                    [
                        'tipe' => 'restock',
                        'icon' => 'fa-boxes-packing',
                        'judul' => 'Rekomendasi Tambah Stok',
                        'pesan' => "Produk {$item['nama']} ({$item['kode']}) terdeteksi Sangat Laku dengan total {$item['terjual']} item terjual minggu ini. Disarankan melakukan restock."
                    ]
                );
            }
            
            // 3. Kondisi Stok Mengendap / Overstock (Barang Macet)
            if ($item['label'] === 'Kurang Laris' && $item['terjual'] === 0 && $item['stok'] >= 30) {
                Notifikasi::updateOrCreate(
                    ['id_item' => 'overstock_' . $item['kode'] . '_' . $periodeKey],
                    [
                        'tipe' => 'overstock',
                        'icon' => 'fa-layer-group',
                        'judul' => 'Stok Mengendap (Macet)',
                        'pesan' => "Stok menumpuk sebanyak {$item['stok']} item pada produk {$item['nama']} ({$item['kode']}) di gudang tanpa ada catatan penjualan minggu ini."
                    ]
                );
            }
            
            // 4. Kondisi Hasil Cluster Kurang Laris Terjual Sedikit (Low Sales)
            if ($item['label'] === 'Kurang Laris' && $item['terjual'] > 0) {
                Notifikasi::updateOrCreate(
                    ['id_item' => 'low-sales_' . $item['kode'] . '_' . $periodeKey],
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