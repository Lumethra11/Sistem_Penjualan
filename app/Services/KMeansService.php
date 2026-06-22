<?php

namespace App\Services;

use App\Models\Barang;
use App\Models\RiwayatClustering;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
                $item['label_cluster'] = ($item['terjual'] > 0) ? 'Sedang' : 'Kurang Laris';
                return (object)$item;
            });
        }

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
            if ($item['terjual'] === 0) {
                $item['label_cluster'] = 'Kurang Laris';
            } else {
                $item['label_cluster'] = $assignments[$item['kode_barang']] ?? 'Kurang Laris';
            }
            return (object)$item;
        })->values();
    }

    public function saveClusterHistoryAndNotification($startDate, $endDate)
    {
        Carbon::setLocale('id');
        date_default_timezone_set('Asia/Jakarta');

        $results = $this->calculateKMeansRealtime($startDate, $endDate);

        if ($results->isEmpty()) {
            return false;
        }

        $formatStart = Carbon::parse($startDate)->translatedFormat('d F Y');
        $formatEnd = Carbon::parse($endDate)->translatedFormat('d F Y');
        $periodeLabel = $formatStart . ' s/d ' . $formatEnd;

        RiwayatClustering::where('periode', $periodeLabel)->delete();
        $periodeKey = Carbon::parse($startDate)->format('Ymd');

        foreach ($results as $res) {
            // 1. Simpan ke Tabel Riwayat
            RiwayatClustering::create([
                'tanggal_proses'  => Carbon::now()->toDateString(),
                'periode'         => $periodeLabel,
                'kode_barang'     => $res->kode_barang,
                'nilai_x_stok'    => $res->stok,
                'nilai_y_terjual' => $res->terjual,
                'label_cluster'   => $res->label_cluster,
            ]);

            // 2. Tentukan Konten Notifikasi Berdasarkan Kategori Cluster & Aturan Bisnis
            if ($res->label_cluster === 'Laris') {
                $judulNotif = 'Rekomendasi Tambah Stok';
                $pesanNotif = "Suku cadang {$res->nama_barang} ({$res->kode_barang}) teridentifikasi Laris dengan total {$res->terjual} unit penjualan. Disarankan segera melakukan penambahan stok.";
                $tipeNotif = 'restock';
                $iconNotif = 'fa-boxes-packing';
            } elseif ($res->label_cluster === 'Kurang Laris' && $res->terjual === 0 && $res->stok >= 30) {
                $judulNotif = 'Stok Mengendap (Macet)';
                $pesanNotif = "Stok menumpuk sebanyak {$res->stok} unit pada produk {$res->nama_barang} ({$res->kode_barang}) di gudang tanpa ada catatan transaksi penjualan.";
                $tipeNotif = 'overstock';
                $iconNotif = 'fa-layer-group';
            } elseif ($res->label_cluster === 'Kurang Laris' && $res->terjual > 0) {
                $judulNotif = 'Evaluasi Penjualan Rendah';
                $pesanNotif = "Perputaran unit {$res->nama_barang} ({$res->kode_barang}) lambat, baru mencatatkan {$res->terjual} item penjualan.";
                $tipeNotif = 'low-sales';
                $iconNotif = 'fa-arrow-trend-down';
            } else {
                continue;
            }

            // 3. Simpan ke Tabel Notifikasi Menggunakan Skema Kolom Asli Anda (judul, pesan)
            DB::table('notifikasi')->updateOrInsert(
                ['id_item' => $tipeNotif . '_' . $res->kode_barang . '_' . $periodeKey],
                [
                    'tipe'        => $tipeNotif,
                    'icon'        => $iconNotif,
                    'judul'       => $judulNotif,
                    'pesan'       => $pesanNotif,
                    'is_read'     => false,
                    'created_at'  => Carbon::now(),
                    'updated_at'  => Carbon::now()
                ]
            );
        }

        return true;
    }
}