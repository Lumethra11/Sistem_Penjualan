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
        $maxStok = 1; $minStok = INF;
        $maxTerjual = 1; $minTerjual = INF;

        // Langkah 1: Bangun dataset & cari nilai Min-Max untuk normalisasi
        foreach ($barangList as $b) {
            $terjualCount = isset($penjualan[$b->id]) ? (int)$penjualan[$b->id] : 0;
            $stokCount = (int)$b->stok;

            if ($stokCount > $maxStok) $maxStok = $stokCount;
            if ($stokCount < $minStok) $minStok = $stokCount;
            if ($terjualCount > $maxTerjual) $maxTerjual = $terjualCount;
            if ($terjualCount < $minTerjual) $minTerjual = $terjualCount;

            $dataset[] = [
                'id_barang'   => $b->id,
                'kode_barang' => $b->kode_barang,
                'nama_barang' => $b->nama_barang,
                'stok'        => $stokCount,
                'terjual'     => $terjualCount
            ];
        }

        // Antisipasi jika semua nilai stok atau terjual sama (menghindari pembagian dengan nol)
        $rangeStok = ($maxStok - $minStok) == 0 ? 1 : ($maxStok - $minStok);
        $rangeTerjual = ($maxTerjual - $minTerjual) == 0 ? 1 : ($maxTerjual - $minTerjual);

        if (count($dataset) < 3) {
            return collect($dataset)->map(function($item) {
                $item['label_cluster'] = ($item['terjual'] > 0) ? 'Sedang' : 'Kurang Laris';
                return (object)$item;
            });
        }

        // Langkah 2: Tentukan Centroid Awal berdasarkan urutan data terjual (seperti Excel Anda)
        $sortedSales = collect($dataset)->sortBy('terjual')->values()->toArray();
        $count = count($sortedSales);

        $rawCentroids = [
            'Laris'        => $sortedSales[$count - 1],
            'Sedang'       => $sortedSales[floor($count / 2)],
            'Kurang Laris' => $sortedSales[0]
        ];

        // Lakukan normalisasi pada Centroid Awal
        $centroids = [];
        foreach ($rawCentroids as $label => $c) {
            $centroids[$label] = [
                'stok'    => ($c['stok'] - $minStok) / $rangeStok,
                'terjual' => ($c['terjual'] - $minTerjual) / $rangeTerjual
            ];
        }

        $assignments = [];

        // Langkah 3: Iterasi K-Means
        for ($iter = 0; $iter < $maxIterations; $iter++) {
            $newGroups = ['Laris' => [], 'Sedang' => [], 'Kurang Laris' => []];
            $changed = false;

            foreach ($dataset as $data) {
                // Normalisasikan data yang sedang dihitung jaraknya
                $normStok = ($data['stok'] - $minStok) / $rangeStok;
                $normTerjual = ($data['terjual'] - $minTerjual) / $rangeTerjual;

                $closestCluster = null;
                $minDistance = INF;

                foreach ($centroids as $label => $centroid) {
                    // Hitung Euclidean Distance menggunakan data berskala seimbang (0 sampai 1)
                    $dist = sqrt(
                        pow($normTerjual - $centroid['terjual'], 2) +
                        pow($normStok - $centroid['stok'], 2)
                    );

                    if ($dist < $minDistance) {
                        $minDistance = $dist;
                        $closestCluster = $label;
                    }
                }

                $newGroups[$closestCluster][] = [
                    'stok'    => $normStok,
                    'terjual' => $normTerjual,
                    'raw'     => $data
                ];

                if (!isset($assignments[$data['kode_barang']]) || $assignments[$data['kode_barang']] !== $closestCluster) {
                    $assignments[$data['kode_barang']] = $closestCluster;
                    $changed = true;
                }
            }

            if (!$changed) {
                break;
            }

            // Hitung ulang posisi Centroid (Mencari rata-rata baru dari data ter-normalisasi)
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

        // Langkah 4: Kembalikan format data asli beserta label klasternya
        return collect($dataset)->map(function($item) use ($assignments) {
            $item['label_cluster'] = $assignments[$item['kode_barang']] ?? 'Kurang Laris';
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

            // 2. Tentukan Konten Notifikasi Berdasarkan Kategori Cluster
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

            // 3. Simpan ke Tabel Notifikasi
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