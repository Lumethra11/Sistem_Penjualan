<?php

namespace App\Services;

use App\Models\Barang;
use Illuminate\Support\Facades\DB;

class KMeansService
{
    public function calculateKMeansRealtime($startDate, $endDate, $maxIterations = 100)
    {
        $barangList = Barang::where('tipe_barang', 'stok')->get();

        if ($barangList->isEmpty()) {
            return collect([]);
        }

        $penjualan = DB::table('detail_transaksi')
            ->join('transaksi', 'detail_transaksi.transaksi_id', '=', 'transaksi.id')
            ->where('transaksi.status', 'selesai')
            ->whereBetween('transaksi.created_at', [$startDate, $endDate])
            ->select('detail_transaksi.barang_id', DB::raw('SUM(detail_transaksi.jumlah) as total_terjual'))
            ->groupBy('detail_transaksi.barang_id')
            ->pluck('total_terjual', 'barang_id')
            ->toArray();

        // VALIDASI UTAMA: Jika tidak ada satupun barang yang terjual di minggu ini, 
        // Maka langsung kembalikan array kosong agar tampilan di halaman web bersih (Kosong)
        if (empty($penjualan) || array_sum($penjualan) === 0) {
            return collect([]);
        }

        $dataset = [];
        foreach ($barangList as $b) {
            $terjualCount = isset($penjualan[$b->id]) ? (int)$penjualan[$b->id] : 0;
            
            $dataset[] = [
                'id_barang'   => $b->id,
                'kode_barang' => $b->kode_barang,
                'nama_barang' => $b->nama_barang,
                'stok'        => (int)$b->stok,
                'terjual'     => $terjualCount
            ];
        }

        if (count($dataset) < 3) {
            return collect($dataset)->map(function($item) {
                // Jaga-jaga jika terjual 0 mutlak Kurang Laris
                $item['label_cluster'] = ($item['terjual'] > 0) ? 'Sedang' : 'Kurang Laris';
                return (object)$item;
            });
        }

        // Ambil inisialisasi centroid awal dari data riil
        $sortedSales = collect($dataset)->sortBy('terjual')->values()->toArray();
        $count = count($sortedSales);

        $centroids = [
            'Laris'        => ['terjual' => $sortedSales[$count - 1]['terjual'], 'stok' => $sortedSales[$count - 1]['stok']],
            'Sedang'       => ['terjual' => $sortedSales[floor($count / 2)]['terjual'], 'stok' => $sortedSales[floor($count / 2)]['stok']],
            'Kurang Laris' => ['terjual' => $sortedSales[0]['terjual'], 'stok' => $sortedSales[0]['stok']]
        ];

        $assignments = [];

        for ($iter = 0; $iter < $maxIterations; $iter++) {
            $newGroups = ['Laris' => [], 'Sedang' => [], 'Kurang Laris' => []];
            $changed = false;

            foreach ($dataset as $data) {
                // ATURAN MUTLAK: Menyelesaikan misteri terjual 0 masuk cluster Laris.
                // Jika tidak ada penjualan sama sekali, secara paksa dikunci ke Kurang Laris.
                if ($data['terjual'] === 0) {
                    $closestCluster = 'Kurang Laris';
                } else {
                    $closestCluster = 'Kurang Laris';
                    $minDistance = INF;

                    foreach ($centroids as $label => $centroid) {
                        $dist = sqrt(
                            pow($data['terjual'] - $centroid['terjual'], 2) + 
                            pow($data['stok'] - $centroid['stok'], 2)
                        );

                        if ($dist < $minDistance) {
                            $minDistance = $dist;
                            $closestCluster = $label;
                        }
                    }
                }

                $newGroups[$closestCluster][] = $data;

                if (!isset($assignments[$data['kode_barang']]) || $assignments[$data['kode_barang']] !== $closestCluster) {
                    $assignments[$data['kode_barang']] = $closestCluster;
                    $changed = true;
                }
            }

            if (!$changed) {
                break;
            }

            foreach ($newGroups as $label => $items) {
                if (count($items) > 0) {
                    $sumTerjual = 0;
                    $sumStok = 0;
                    foreach ($items as $item) {
                        $sumTerjual += $item['terjual'];
                        $sumStok += $item['stok'];
                    }
                    $centroids[$label] = [
                        'terjual' => $sumTerjual / count($items),
                        'stok'    => $sumStok / count($items)
                    ];
                }
            }
        }

        return collect($dataset)->map(function($item) use ($assignments) {
            // Pengamanan tingkat akhir untuk item tidak terjual
            if ($item['terjual'] === 0) {
                $item['label_cluster'] = 'Kurang Laris';
            } else {
                $item['label_cluster'] = $assignments[$item['kode_barang']] ?? 'Kurang Laris';
            }
            return (object)$item;
        })->values();
    }
}