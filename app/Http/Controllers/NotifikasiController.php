<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\DetailTransaksi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class NotifikasiController extends Controller
{
    public function index(Request $request)
    {
        $now = Carbon::now();
        $stringPeriodeMingguIni = 'Minggu ' . $now->weekOfMonth . ' Bulan ' . $now->format('F Y');

        $allNotifications = $this->hitungNotifikasiLive();

        // Pagination manual 10 data per halaman
        $perPage = 10;
        $currentPage = Paginator::resolveCurrentPage('page') ?: 1;
        $currentItems = array_slice($allNotifications, ($currentPage - 1) * $perPage, $perPage);
        
        $paginatedNotifications = new LengthAwarePaginator(
            $currentItems, 
            count($allNotifications), 
            $perPage, 
            $currentPage, 
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // KUNCI PERBAIKAN: Paksa paginator manual menggunakan view simple-bootstrap / bootstrap agar HTML-nya bersih dari Tailwind raksasa
        $paginatedNotifications->withPath($request->url());

        $groupedNotifications = [];
        foreach ($paginatedNotifications as $item) {
            $groupedNotifications[$item['tanggal_group']][] = $item;
        }

        return view('notifikasi.index', [
            'groupedNotifications' => $groupedNotifications,
            'allNotifications' => $paginatedNotifications,
            'stringPeriodeMingguIni' => $stringPeriodeMingguIni
        ]);
    }

    public function getNotificationsJson()
    {
        $allNotifications = $this->hitungNotifikasiLive();
        
        return response()->json([
            'status' => 'success',
            'total' => count($allNotifications),
            'data' => $allNotifications
        ]);
    }

    private function hitungNotifikasiLive()
    {
        $start = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $end = Carbon::now()->endOfWeek(Carbon::SUNDAY);
        
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
                'label' => 'Sedang',
                'raw_date' => $b->updated_at ? Carbon::parse($b->updated_at) : Carbon::now()
            ];
        }

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

        $listNotifikasi = [];
        foreach ($dataset as $item) {
            if ($item['stok'] <= 5) {
                $listNotifikasi[] = [
                    'id_item' => 'stok_' . $item['kode'],
                    'tipe' => 'stok-out',
                    'icon' => 'fa-triangle-exclamation',
                    'judul' => 'Stok Kritis / Habis',
                    'kode' => $item['kode'],
                    'nama' => $item['nama'],
                    'tanggal_group' => $item['raw_date']->format('d M Y'),
                    'waktu' => $item['raw_date']->format('H:i'),
                    'pesan' => "Sisa stok produk tinggal {$item['stok']} item. Segera ajukan pemesanan ulang."
                ];
            }
            if ($item['label'] === 'Laris') {
                $listNotifikasi[] = [
                    'id_item' => 'restock_' . $item['kode'],
                    'tipe' => 'restock',
                    'icon' => 'fa-boxes-packing',
                    'judul' => 'Rekomendasi Tambah Stok',
                    'kode' => $item['kode'],
                    'nama' => $item['nama'],
                    'tanggal_group' => Carbon::now()->startOfWeek()->format('d M Y'),
                    'waktu' => '08:00',
                    'pesan' => "Produk terdeteksi Sangat Laku dengan {$item['terjual']} item terjual minggu ini."
                ];
            }
            if ($item['label'] === 'Kurang Laris' && $item['terjual'] === 0 && $item['stok'] >= 30) {
                $listNotifikasi[] = [
                    'id_item' => 'overstock_' . $item['kode'],
                    'tipe' => 'overstock',
                    'icon' => 'fa-layer-group',
                    'judul' => 'Stok Mengendap (Macet)',
                    'kode' => $item['kode'],
                    'nama' => $item['nama'],
                    'tanggal_group' => Carbon::now()->startOfWeek()->format('d M Y'),
                    'waktu' => '08:00',
                    'pesan' => "Stok menumpuk sebanyak {$item['stok']} item di gudang tanpa ada penjualan minggu ini."
                ];
            }
            if ($item['label'] === 'Kurang Laris' && $item['terjual'] > 0) {
                $listNotifikasi[] = [
                    'id_item' => 'low-sales_' . $item['kode'],
                    'tipe' => 'low-sales',
                    'icon' => 'fa-arrow-trend-down',
                    'judul' => 'Evaluasi Penjualan Rendah',
                    'kode' => $item['kode'],
                    'nama' => $item['nama'],
                    'tanggal_group' => Carbon::now()->startOfWeek()->format('d M Y'),
                    'waktu' => '08:00',
                    'pesan' => "Perputaran produk lambat, baru berhasil mencatatkan {$item['terjual']} penjualan item."
                ];
            }
        }

        return $listNotifikasi;
    }
}