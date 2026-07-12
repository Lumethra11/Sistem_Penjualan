<?php

namespace App\Services;

use App\Models\Barang;
use App\Models\RiwayatClustering;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KMeansService
{
    /**
     * KALKULASI ALGORITMA K-MEANS TER-NORMALISASI (Terisolasi Per Bengkel/Tenant)
     */
    public function calculateKMeansRealtime($startDate, $endDate, $maxIterations = 100)
    {
        $user = auth()->user();
        // JANGKAR MULTI-TENANT: Mengamankan hak kepemilikan data pada ID Admin (Pemilik Bengkel)
        $adminId = ($user->role === 'admin') ? $user->id : $user->admin_id;

        // -------------------------------------------------------------------------
        // LANGKAH 1: Bangun Dataset Khusus Bengkel Ini & Cari Nilai Min-Max
        // -------------------------------------------------------------------------
        $barangList = Barang::where('user_id', $adminId)
            ->where('tipe_barang', 'stok')
            ->get();

        if ($barangList->isEmpty()) {
            return collect([]);
        }

        // Mengambil akumulasi jumlah terjual dengan filter keamanan ketat lingkup internal bengkel
        $penjualan = DB::table('detail_transaksi')
            ->join('transaksi', 'detail_transaksi.transaksi_id', '=', 'transaksi.id')
            ->join('users', 'transaksi.user_id', '=', 'users.id')
            ->where('transaksi.status', 'selesai')
            ->where(function($q) use ($adminId) {
                $q->where('users.id', $adminId)->orWhere('users.admin_id', $adminId);
            })
            ->whereBetween('transaksi.created_at', [$startDate, $endDate])
            ->select('detail_transaksi.barang_id', DB::raw('SUM(detail_transaksi.jumlah) as total_terjual'))
            ->groupBy('detail_transaksi.barang_id')
            ->pluck('total_terjual', 'detail_transaksi.barang_id')
            ->toArray();

        if (empty($penjualan) || array_sum($penjualan) === 0) {
            return collect([]);
        }

        $dataset = [];
        $allSalesValues = [];
        $maxStok = 1; $minStok = INF;
        $maxTerjual = 1; $minTerjual = INF;

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

            $allSalesValues[] = $terjualCount;
        }

        $countData = count($dataset);
        if ($countData < 3) {
            return collect($dataset)->map(function($item) {
                $item['label_cluster'] = ($item['terjual'] > 0) ? 'Sedang' : 'Kurang Laris';
                return (object)$item;
            });
        }

        // Antisipasi pembagian dengan nol (jika variasi nilai data homogen)
        $rangeStok = ($maxStok - $minStok) == 0 ? 1 : ($maxStok - $minStok);
        $rangeTerjual = ($maxTerjual - $minTerjual) == 0 ? 1 : ($maxTerjual - $minTerjual);

        // -------------------------------------------------------------------------
        // LANGKAH 2: Tentukan Centroid Awal Matematika Berbasis Median Data Internal
        // -------------------------------------------------------------------------
        sort($allSalesValues);
        $minSales = $allSalesValues[0];
        $maxSales = $allSalesValues[$countData - 1];
        
        $midIndex = floor($countData / 2);
        if ($countData % 2 === 0) {
            $medianSales = ($allSalesValues[$midIndex - 1] + $allSalesValues[$midIndex]) / 2;
        } else {
            $medianSales = $allSalesValues[$midIndex];
        }

        $cMax = collect($dataset)->where('terjual', $maxSales)->first() ?? $dataset[$countData - 1];
        $cMin = collect($dataset)->where('terjual', $minSales)->first() ?? $dataset[0];
        $cMid = collect($dataset)->sortBy(function($item) use ($medianSales) {
            return abs($item['terjual'] - $medianSales);
        })->first() ?? $dataset[$midIndex];

        // Konversi koordinat awal klaster ke skala normalisasi (0 s/d 1)
        $centroids = [
            'Laris' => [
                'stok'    => ($cMax['stok'] - $minStok) / $rangeStok,
                'terjual' => ($cMax['terjual'] - $minTerjual) / $rangeTerjual
            ],
            'Sedang' => [
                'stok'    => ($cMid['stok'] - $minStok) / $rangeStok,
                'terjual' => ($cMid['terjual'] - $minTerjual) / $rangeTerjual
            ],
            'Kurang Laris' => [
                'stok'    => ($cMin['stok'] - $minStok) / $rangeStok,
                'terjual' => ($cMin['terjual'] - $minTerjual) / $rangeTerjual
            ]
        ];

        $assignments = [];

        // -------------------------------------------------------------------------
        // LANGKAH 3: Iterasi Pencarian Konvergensi Titik Pusat (Clustering Loop)
        // -------------------------------------------------------------------------
        for ($iter = 0; $iter < $maxIterations; $iter++) {
            $newGroups = ['Laris' => [], 'Sedang' => [], 'Kurang Laris' => []];
            $changed = false;

            foreach ($dataset as $data) {
                $normStok = ($data['stok'] - $minStok) / $rangeStok;
                $normTerjual = ($data['terjual'] - $minTerjual) / $rangeTerjual;

                $closestCluster = null;
                $minDistance = INF;

                foreach ($centroids as $label => $centroid) {
                    // Hitung Euclidean Distance Ter-normalisasi
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
                    'terjual' => $normTerjual
                ];

                if (!isset($assignments[$data['kode_barang']]) || $assignments[$data['kode_barang']] !== $closestCluster) {
                    $assignments[$data['kode_barang']] = $closestCluster;
                    $changed = true;
                }
            }

            if (!$changed) {
                break;
            }

            // -------------------------------------------------------------------------
            // LANGKAH 4: Hitung Ulang Pergeseran Centroid Baru (Rata-Rata Kelompok)
            // -------------------------------------------------------------------------
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

        // -------------------------------------------------------------------------
        // LANGKAH 5: Kembalikan Output Nilai Asli yang Dilabeli Hasil Akhir K-Means
        // -------------------------------------------------------------------------
        return collect($dataset)->map(function($item) use ($assignments) {
            $item['label_cluster'] = $assignments[$item['kode_barang']] ?? 'Kurang Laris';
            return (object)$item;
        })->values();
    }

    /**
     * SIMPAN DATA STRUKTUR HISTORI CLUSTERING DAN AUTOMATISASI NOTIFIKASI BARANG
     */
    public function saveClusterHistoryAndNotification($startDate, $endDate)
    {
        Carbon::setLocale('id');
        date_default_timezone_set('Asia/Jakarta');

        $user = auth()->user();
        $adminId = ($user->role === 'admin') ? $user->id : $user->admin_id;

        $results = $this->calculateKMeansRealtime($startDate, $endDate);

        if ($results->isEmpty()) {
            return false;
        }

        $formatStart = Carbon::parse($startDate)->translatedFormat('d F Y');
        $formatEnd = Carbon::parse($endDate)->translatedFormat('d F Y');
        $periodeLabel = $formatStart . ' s/d ' . $formatEnd;

        // Amankan proses hapus: Hanya membersihkan riwayat periode ini MILIK BENGKEL INI
        RiwayatClustering::where('user_id', $adminId)->where('periode', $periodeLabel)->delete();
        $periodeKey = Carbon::parse($startDate)->format('Ymd');

        foreach ($results as $res) {
            // 1. Simpan Rekam Jejak ke Tabel Riwayat dengan Pengunci user_id Admin
            RiwayatClustering::create([
                'user_id'         => $adminId, 
                'tanggal_proses'  => Carbon::now()->toDateString(),
                'periode'         => $periodeLabel,
                'kode_barang'     => $res->kode_barang,
                'nilai_x_stok'    => $res->stok,
                'nilai_y_terjual' => $res->terjual,
                'label_cluster'   => $res->label_cluster,
            ]);

            // 2. Evaluasi Kondisi Hasil Klasterisasi Pemasaran
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

            // 3. Simpan / Perbarui Notifikasi Menggunakan Komponen Token Multi-tenant Unik
            DB::table('notifikasi')->updateOrInsert(
                [
                    'user_id' => $adminId,
                    'id_item' => $tipeNotif . '_' . $adminId . '_' . $res->kode_barang . '_' . $periodeKey
                ],
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