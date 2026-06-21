<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\RiwayatClustering;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('filter', 'hari');
        $now = Carbon::now();

        // 1. Jalankan Auto-Save Masa Lalu (Mengunci Hasil Minggu Lalu Otomatis Setiap Hari Senin)
        $this->PROSES_AUTO_SAVE_KMEANS_MINGGUAN();

        // 2. Total Ragam Jenis Barang Global
        $totalBarang = Barang::count();

        // 3. Query Utama untuk Card Indikator
        $queryPemasukan = Transaksi::where('status', 'selesai');
        $queryPengeluaran = DetailTransaksi::whereHas('transaksi', function ($q) {
            $q->where('status', 'selesai');
        });

        if ($filter === 'hari') {
            $queryPemasukan->whereDate('created_at', Carbon::today());
            $queryPengeluaran->whereDate('created_at', Carbon::today());
        } elseif ($filter === 'minggu') {
            $startOfWeek = Carbon::now()->startOfWeek(Carbon::MONDAY);
            $endOfWeek = Carbon::now()->endOfWeek(Carbon::SUNDAY);
            $queryPemasukan->whereBetween('created_at', [$startOfWeek, $endOfWeek]);
            $queryPengeluaran->whereHas('transaksi', function ($q) use ($startOfWeek, $endOfWeek) {
                $q->whereBetween('created_at', [$startOfWeek, $endOfWeek]);
            });
        } elseif ($filter === 'bulan') {
            $queryPemasukan->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year);
            $queryPengeluaran->whereHas('transaksi', function ($q) use ($now) {
                $q->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year);
            });
        }

        // Hitung nominal 4 Card Utama
        $totalPemasukan = (clone $queryPemasukan)->sum('total_harga');
        
        $rawPengeluaran = (clone $queryPengeluaran)->get();
        $totalPengeluaran = 0;
        foreach ($rawPengeluaran as $rp) {
            $modalSatuan = $rp->harga_modal > 0 ? $rp->harga_modal : ($rp->barang ? $rp->barang->harga_beli : 0);
            $totalPengeluaran += ($modalSatuan * $rp->jumlah);
        }

        $stokRendah = Barang::where('tipe_barang', 'stok')->where('stok', '<=', 10)->count();

        // 4. LOGIKA GRAFIK DINAMIS
        $chartLabels = []; $chartPemasukan = []; $chartPengeluaran = [];
        if ($filter === 'hari') {
            $chartLabels = ['08:00', '10:00', '12:00', '14:00', '16:00', '17:00+'];
            $hoursRange = [['00:00:00', '09:59:59'], ['10:00:00', '11:59:59'], ['12:00:00', '13:59:59'], ['14:00:00', '15:59:59'], ['16:00:00', '16:59:59'], ['17:00:00', '23:59:59']];
            foreach ($hoursRange as $range) {
                $chartPemasukan[] = Transaksi::where('status', 'selesai')->whereDate('created_at', Carbon::today())->whereTime('created_at', '>=', $range[0])->whereTime('created_at', '<=', $range[1])->sum('total_harga');
                $detailsHour = DetailTransaksi::whereHas('transaksi', function($q) use ($range) { $q->where('status', 'selesai')->whereDate('created_at', Carbon::today())->whereTime('created_at', '>=', $range[0])->whereTime('created_at', '<=', $range[1]); })->with('barang')->get();
                $modalHourSum = 0;
                foreach ($detailsHour as $dh) { $itemModal = $dh->harga_modal > 0 ? $dh->harga_modal : ($dh->barang ? $dh->barang->harga_beli : 0); $modalHourSum += ($itemModal * $dh->jumlah); }
                $chartPengeluaran[] = $modalHourSum;
            }
        } elseif ($filter === 'minggu') {
            $chartLabels = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
            $mapDays = [2, 3, 4, 5, 6, 7, 1];
            $startW = Carbon::now()->startOfWeek(Carbon::MONDAY);
            $endW = Carbon::now()->endOfWeek(Carbon::SUNDAY);
            $pemasukanMingguan = Transaksi::where('status', 'selesai')->whereBetween('created_at', [$startW, $endW])->select(DB::raw('DAYOFWEEK(created_at) as hari'), DB::raw('SUM(total_harga) as total'))->groupBy('hari')->get()->pluck('total', 'hari')->toArray();
            $pengeluaranRawMingguan = DetailTransaksi::whereHas('transaksi', function ($q) use ($startW, $endW) { $q->where('status', 'selesai')->whereBetween('created_at', [$startW, $endW]); })->with('barang')->join('transaksi', 'detail_transaksi.transaksi_id', '=', 'transaksi.id')->select('detail_transaksi.*', 'transaksi.created_at as tx_date')->get();
            $pengeluaranMingguan = [];
            foreach ($pengeluaranRawMingguan as $prm) { $dayIndex = Carbon::parse($prm->tx_date)->dayOfWeek + 1; $modalHarga = $prm->harga_modal > 0 ? $prm->harga_modal : ($prm->barang ? $prm->barang->harga_beli : 0); $pengeluaranMingguan[$dayIndex] = ($pengeluaranMingguan[$dayIndex] ?? 0) + ($modalHarga * $prm->jumlah); }
            foreach ($mapDays as $day) { $chartPemasukan[] = $pemasukanMingguan[$day] ?? 0; $chartPengeluaran[] = $pengeluaranMingguan[$day] ?? 0; }
        } elseif ($filter === 'bulan') {
            $chartLabels = ['Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4', 'Sisa Hari'];
            $weeksRange = [[1, 7], [8, 14], [15, 21], [22, 28], [29, 31]];
            foreach ($weeksRange as $wRange) {
                $chartPemasukan[] = Transaksi::where('status', 'selesai')->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->whereDay('created_at', '>=', $wRange[0])->whereDay('created_at', '<=', $wRange[1])->sum('total_harga');
                $detailsWeek = DetailTransaksi::whereHas('transaksi', function($q) use ($now, $wRange) { $q->where('status', 'selesai')->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->whereDay('created_at', '>=', $wRange[0])->whereDay('created_at', '<=', $wRange[1]); })->with('barang')->get();
                $modalWeekSum = 0;
                foreach ($detailsWeek as $dw) { $itemModal = $dw->harga_modal > 0 ? $dw->harga_modal : ($dw->barang ? $dw->barang->harga_beli : 0); $modalWeekSum += ($itemModal * $dw->jumlah); }
                $chartPengeluaran[] = $modalWeekSum;
            }
        }

        $pendapatanJasa = (clone $queryPemasukan)->sum('biaya_jasa_servis');
        $pendapatanBarang = $totalPemasukan - $pendapatanJasa;
        $detailBarangPemasukan = (clone $queryPengeluaran)->select('barang_id', 'nama_barang_manual', DB::raw('SUM(jumlah) as total_qty'), DB::raw('SUM(harga_jual_satuan * jumlah) as total_jual'))->groupBy('barang_id', 'nama_barang_manual')->get();
        foreach ($detailBarangPemasukan as $item) { $item->nama = $item->nama_produk; $item->harga_beli_satuan = $item->barang_id ? ($item->barang ? $item->barang->harga_beli : 0) : 0; $item->total_modal = $item->harga_beli_satuan * $item->total_qty; $item->keuntungan = $item->total_jual - $item->total_modal; }
        $detailJasaPemasukan = (clone $queryPemasukan)->where('biaya_jasa_servis', '>', 0)->select('jenis_motor', 'biaya_jasa_servis as nominal')->get();

        // 5. AMBIL DATA SNAPSHOT K-MEANS REALTIME WEEK BERJALAN
        $liveKMeans = $this->HITUNG_LOGIKA_KMEANS_LIVE(Carbon::now()->startOfWeek(Carbon::MONDAY), Carbon::now()->endOfWeek(Carbon::SUNDAY));

        return view('dashboard.index', [
            'totalBarang' => $totalBarang, 'pemasukan' => $totalPemasukan, 'pengeluaran' => $totalPengeluaran, 'stokRendah' => $stokRendah,
            'chartPemasukan' => $chartPemasukan, 'chartPengeluaran' => $chartPengeluaran, 'chartLabels' => $chartLabels, 'filter' => $filter,
            'pendapatanBarang' => $pendapatanBarang, 'pendapatanJasa' => $pendapatanJasa, 'detailBarangPemasukan' => $detailBarangPemasukan,
            'detailJasaPemasukan' => $detailJasaPemasukan, 'barangTerlaris' => $liveKMeans
        ]);
    }

    public function clusteringDetail(Request $request)
    {
        $now = Carbon::now();
        $bulanFilter = $request->input('bulan', $now->format('Y-m'));
        $mingguFilter = $request->input('minggu', 'Minggu ' . $now->weekOfMonth);
        $labelFilter = $request->input('label_cluster', 'all');

        // Mapping konversi dari filter operasional ke kolom ENUM database riwayat_clustering
        $dbLabelMap = [
            'Laris' => 'Laris',
            'Sedang' => 'Sedang',
            'Kurang Laris' => 'Kurang Laris'
        ];

        $parseDate = Carbon::parse($bulanFilter . '-01');
        $stringPeriodeTarget = $mingguFilter . ' Bulan ' . $parseDate->format('F Y');
        $stringPeriodeMingguIni = 'Minggu ' . $now->weekOfMonth . ' Bulan ' . $now->format('F Y');

        $records = collect();

        if ($stringPeriodeTarget === $stringPeriodeMingguIni) {
            $liveData = $this->HITUNG_LOGIKA_KMEANS_LIVE(Carbon::now()->startOfWeek(Carbon::MONDAY), Carbon::now()->endOfWeek(Carbon::SUNDAY));
            foreach ($liveData as $ld) {
                if ($labelFilter === 'all' || $ld['label'] === $ld['label_asli']) {
                    $records->push((object) [
                        'kode_barang' => $ld['kode'],
                        'nama_barang_live' => $ld['nama'],
                        'nilai_x_stok' => $ld['stok'],
                        'nilai_y_terjual' => $ld['terjual'],
                        'label_cluster' => $ld['label'] // Menggunakan teks operasional bengkel
                    ]);
                }
            }
        } else {
            $dbData = RiwayatClustering::with('barang')->where('periode', $stringPeriodeTarget);
            if ($labelFilter !== 'all') { 
                $dbData->where('label_cluster', $dbLabelMap[$labelFilter]); 
            }
            $dbRecords = $dbData->get();

            // Mapping bahasa DB ke bahasa operasional bengkel saat penarikan arsip lawas
            foreach ($dbRecords as $rec) {
                $labelTeks = 'Kurang Laku';
                if ($rec->label_cluster === 'Laris') $labelTeks = 'Sangat Laku';
                if ($rec->label_cluster === 'Sedang') $labelTeks = 'Laku Normal';

                $records->push((object) [
                    'kode_barang' => $rec->kode_barang,
                    'nama_barang_live' => $rec->barang->nama_barang ?? 'Produk Dihapus',
                    'nilai_x_stok' => $rec->nilai_x_stok,
                    'nilai_y_terjual' => $rec->nilai_y_terjual,
                    'label_cluster' => $labelTeks
                ]);
            }
        }

        $opsiPeriodeBulan = RiwayatClustering::select('tanggal_proses')->distinct()->get()->map(function($d) {
            return Carbon::parse($d->tanggal_proses)->format('Y-m');
        })->push($now->format('Y-m'))->unique();

        return view('dashboard.clustering_detail', compact('records', 'bulanFilter', 'mingguFilter', 'labelFilter', 'opsiPeriodeBulan', 'stringPeriodeTarget', 'stringPeriodeMingguIni'));
    }

    private function HITUNG_LOGIKA_KMEANS_LIVE($start, $end)
    {
        $semuaBarang = Barang::all();
        $dataset = [];

        foreach ($semuaBarang as $b) {
            $totalTerjual = DetailTransaksi::where('barang_id', $b->id)->whereHas('transaksi', function($q) use ($start, $end) {
                $q->where('status', 'selesai')->whereBetween('created_at', [$start, $end]);
            })->sum('jumlah');

            $dataset[] = [
                'kode' => $b->kode_barang, 'nama' => $b->nama_barang,
                'stok' => (int)$b->stok, 'terjual' => (int)$totalTerjual, 'label' => 'Sedang'
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

            for ($i = 0; $i < 20; $i++) {
                $clusterBaru = ['Laris' => [], 'Sedang' => [], 'Kurang Laris' => []];
                foreach ($dataset as $idx => $data) {
                    $jarakLaris  = sqrt(pow($data['stok'] - $centroids['Laris']['stok'], 2) + pow($data['terjual'] - $centroids['Laris']['terjual'], 2));
                    $jarakSedang = sqrt(pow($data['stok'] - $centroids['Sedang']['stok'], 2) + pow($data['terjual'] - $centroids['Sedang']['terjual'], 2));
                    $jarakKurang = sqrt(pow($data['stok'] - $centroids['Kurang Laris']['stok'], 2) + pow($data['terjual'] - $centroids['Kurang Laris']['terjual'], 2));
                    $minJarak = min($jarakLaris, $jarakSedang, $jarakKurang);
                    
                    $label = ($minJarak == $jarakLaris) ? 'Laris' : (($minJarak == $jarakSedang) ? 'Sedang' : 'Kurang Laris');
                    
                    // PROTEKSI ABSOLUT ANGKA 0: Paksa barang yang tidak terjual masuk ke kelompok Kurang Laku
                    if ($data['terjual'] === 0) {
                        $label = 'Kurang Laris';
                    }

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
        
        // Transformasikan penamaan label bahasa developer ke bahasa operasional owner bengkel sebelum dirender ke Blade
        return collect($dataset)->map(function($item) {
            $item['label_asli'] = $item['label']; // simpan pembanding filter
            if ($item['label'] === 'Laris') $item['label'] = 'Sangat Laku';
            elseif ($item['label'] === 'Sedang') $item['label'] = 'Laku Normal';
            else $item['label'] = 'Kurang Laku';
            return $item;
        })->sortByDesc('terjual')->values()->all();
    }

    private function PROSES_AUTO_SAVE_KMEANS_MINGGUAN()
    {
        $seninLalu = Carbon::now()->subWeek()->startOfWeek(Carbon::MONDAY);
        $mingguKemarin = Carbon::now()->subWeek()->endOfWeek(Carbon::SUNDAY);
        $periodeStr = 'Minggu ke-' . $seninLalu->weekOfMonth . ' Bulan ' . $seninLalu->format('F Y');

        $sudahAda = RiwayatClustering::where('periode', $periodeStr)->exists();
        if (!$sudahAda) {
            $hasilMingguLalu = $this->HITUNG_LOGIKA_KMEANS_LIVE($seninLalu, $mingguKemarin);
            if (count($hasilMingguLalu) > 0) {
                DB::beginTransaction();
                try {
                    foreach ($hasilMingguLalu as $row) {
                        // Kembalikan nama ke ENUM asli database ('Laris', 'Sedang', 'Kurang Laris') khusus saat save data
                        $dbLabel = 'Kurang Laris';
                        if ($row['label'] === 'Sangat Laku') $dbLabel = 'Laris';
                        if ($row['label'] === 'Laku Normal') $dbLabel = 'Sedang';

                        RiwayatClustering::create([
                            'tanggal_proses'  => Carbon::now()->toDateString(),
                            'periode'         => $periodeStr,
                            'kode_barang'     => $row['kode'],
                            'nilai_x_stok'    => $row['stok'],
                            'nilai_y_terjual' => $row['terjual'],
                            'label_cluster'   => $dbLabel
                        ]);
                    }
                    DB::commit();
                } catch (\Exception $e) { DB::rollBack(); }
            }
        }
    }
}