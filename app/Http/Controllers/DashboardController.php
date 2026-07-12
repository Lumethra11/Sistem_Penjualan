<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\RiwayatClustering;
use App\Services\KMeansService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    protected $kmeansService;

    public function __construct(KMeansService $kmeansService)
    {
        $this->kmeansService = $kmeansService;
    }

    public function index(Request $request)
    {
        Carbon::setLocale('id');
        date_default_timezone_set('Asia/Jakarta');

        $user = Auth::user();
        // JANGKAR MULTI-TENANT: Mengamankan data berdasarkan ID Admin (Pemilik Bengkel)
        $adminId = ($user->role === 'admin') ? $user->id : $user->admin_id;

        $filter = $request->query('filter', 'hari');
        $now = Carbon::now();

        // 1. Total Ragam Jenis Barang (Terfilter per Bengkel)
        $totalBarang = Barang::where('user_id', $adminId)->count();

        // 2. Query Utama untuk Card Indikator (Scoped Otoritas Kru Bengkel)
        $queryPemasukan = Transaksi::where('status', 'selesai')
            ->whereHas('user', function ($qu) use ($adminId) {
                $qu->where('id', $adminId)->orWhere('admin_id', $adminId);
            });

        $queryPengeluaran = DetailTransaksi::whereHas('transaksi', function ($q) use ($adminId) {
            $q->where('status', 'selesai')
              ->whereHas('user', function ($qu) use ($adminId) {
                  $qu->where('id', $adminId)->orWhere('admin_id', $adminId);
              });
        });

        if ($filter === 'hari') {
            $queryPemasukan->whereDate('created_at', Carbon::today());
            $queryPengeluaran->whereDate('created_at', Carbon::today());
        } elseif ($filter === 'minggu') {
            $startOfWeek = Carbon::now()->startOfWeek(Carbon::MONDAY)->startOfDay();
            $endOfWeek = Carbon::now()->endOfWeek(Carbon::SUNDAY)->endOfDay();
            
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

        $totalPemasukan = (clone $queryPemasukan)->sum('total_harga');
        
        $rawPengeluaran = (clone $queryPengeluaran)->with('barang')->get();
        $totalPengeluaran = 0;
        foreach ($rawPengeluaran as $rp) {
            $modalSatuan = $rp->harga_modal > 0 ? $rp->harga_modal : ($rp->barang ? $rp->barang->harga_beli : 0);
            $totalPengeluaran += ($modalSatuan * $rp->jumlah);
        }

        $stokRendah = Barang::where('user_id', $adminId)->where('tipe_barang', 'stok')->where('stok', '<=', 10)->count();

        // 3. Logika Grafik Keuangan Dinamis (Scoped Tenant)
        $chartLabels = []; $chartPemasukan = []; $chartPengeluaran = [];
        if ($filter === 'hari') {
            $chartLabels = ['08:00', '10:00', '12:00', '14:00', '16:00', '17:00+'];
            $hoursRange = [['00:00:00', '09:59:59'], ['10:00:00', '11:59:59'], ['12:00:00', '13:59:59'], ['14:00:00', '15:59:59'], ['16:00:00', '16:59:59'], ['17:00:00', '23:59:59']];
            foreach ($hoursRange as $range) {
                $chartPemasukan[] = (clone $queryPemasukan)->whereTime('created_at', '>=', $range[0])->whereTime('created_at', '<=', $range[1])->sum('total_harga');
                
                $detailsHour = (clone $queryPengeluaran)->whereHas('transaksi', function($q) use ($range) {
                    $q->whereTime('created_at', '>=', $range[0])->whereTime('created_at', '<=', $range[1]);
                })->get();
                
                $modalHourSum = 0;
                foreach ($detailsHour as $dh) { $itemModal = $dh->harga_modal > 0 ? $dh->harga_modal : ($dh->barang ? $dh->barang->harga_beli : 0); $modalHourSum += ($itemModal * $dh->jumlah); }
                $chartPengeluaran[] = $modalHourSum;
            }
        } elseif ($filter === 'minggu') {
            $chartLabels = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
            $mapDays = [2, 3, 4, 5, 6, 7, 1];
            $startW = Carbon::now()->startOfWeek(Carbon::MONDAY)->startOfDay();
            $endW = Carbon::now()->endOfWeek(Carbon::SUNDAY)->endOfDay();
            
            $pemasukanMingguan = (clone $queryPemasukan)->whereBetween('created_at', [$startW, $endW])->select(DB::raw('DAYOFWEEK(created_at) as hari'), DB::raw('SUM(total_harga) as total'))->groupBy('hari')->get()->pluck('total', 'hari')->toArray();
            $pengeluaranRawMingguan = (clone $queryPengeluaran)->whereHas('transaksi', function ($q) use ($startW, $endW) { $q->whereBetween('created_at', [$startW, $endW]); })->join('transaksi', 'detail_transaksi.transaksi_id', '=', 'transaksi.id')->select('detail_transaksi.*', 'transaksi.created_at as tx_date')->get();
            
            $pengeluaranMingguan = [];
            foreach ($pengeluaranRawMingguan as $prm) { $dayIndex = Carbon::parse($prm->tx_date)->dayOfWeek + 1; $modalHarga = $prm->harga_modal > 0 ? $prm->harga_modal : ($prm->barang ? $prm->barang->harga_beli : 0); $pengeluaranMingguan[$dayIndex] = ($pengeluaranMingguan[$dayIndex] ?? 0) + ($modalHarga * $prm->jumlah); }
            foreach ($mapDays as $day) { $chartPemasukan[] = $pemasukanMingguan[$day] ?? 0; $chartPengeluaran[] = $pengeluaranMingguan[$day] ?? 0; }
        } elseif ($filter === 'bulan') {
            $chartLabels = ['Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4', 'Sisa Hari'];
            $weeksRange = [[1, 7], [8, 14], [15, 21], [22, 28], [29, 31]];
            foreach ($weeksRange as $wRange) {
                $chartPemasukan[] = (clone $queryPemasukan)->whereDay('created_at', '>=', $wRange[0])->whereDay('created_at', '<=', $wRange[1])->sum('total_harga');
                
                $detailsWeek = (clone $queryPengeluaran)->whereHas('transaksi', function($q) use ($wRange) {
                    $q->whereDay('created_at', '>=', $wRange[0])->whereDay('created_at', '<=', $wRange[1]);
                })->get();
                
                $modalWeekSum = 0;
                foreach ($detailsWeek as $dw) { $itemModal = $dw->harga_modal > 0 ? $dw->harga_modal : ($dw->barang ? $dw->barang->harga_beli : 0); $modalWeekSum += ($itemModal * $dw->jumlah); }
                $chartPengeluaran[] = $modalWeekSum;
            }
        }

        $pendapatanJasa = (clone $queryPemasukan)->sum('biaya_jasa_servis');
        $pendapatanBarang = $totalPemasukan - $pendapatanJasa;
        
        $detailBarangPemasukan = (clone $queryPengeluaran)->select('barang_id', 'nama_barang_manual', DB::raw('SUM(jumlah) as total_qty'), DB::raw('SUM(harga_jual_satuan * jumlah) as total_jual'))->groupBy('barang_id', 'nama_barang_manual')->get();
        foreach ($detailBarangPemasukan as $item) { $item->nama = $item->nama_barang_manual ?: ($item->barang_id ? ($item->barang ? $item->barang->nama_barang : 'Produk') : 'Produk'); $item->harga_beli_satuan = $item->barang_id ? ($item->barang ? $item->barang->harga_beli : 0) : 0; $item->total_modal = $item->harga_beli_satuan * $item->total_qty; $item->keuntungan = $item->total_jual - $item->total_modal; }
        
        $detailJasaPemasukan = (clone $queryPemasukan)->where('biaya_jasa_servis', '>', 0)->select('jenis_motor', 'biaya_jasa_servis as nominal')->get();

        // 4. PENENTUAN 1 MINGGU LALU SECARA KALENDER ISO
        $startWeekLalu = Carbon::now()->subWeek()->startOfWeek(Carbon::MONDAY)->startOfDay()->toDateTimeString();
        $endWeekLalu = Carbon::now()->subWeek()->endOfWeek(Carbon::SUNDAY)->endOfDay()->toDateTimeString();
        
        $formatStartLalu = Carbon::parse($startWeekLalu)->translatedFormat('d F Y');
        $formatEndLalu = Carbon::parse($endWeekLalu)->translatedFormat('d F Y');
        $periodeLabelLalu = $formatStartLalu . ' s/d ' . $formatEndLalu;

        // Cek riwayat seeder terisolasi user_id
        $cekRiwayatSeeder = RiwayatClustering::where('user_id', $adminId)->where('periode', $periodeLabelLalu)->exists();
        if (!$cekRiwayatSeeder) {
            $this->kmeansService->saveClusterHistoryAndNotification($startWeekLalu, $endWeekLalu);
        }

        // 5. Ambil Hasil Live Clustering K-Means Minggu Berjalan (Terpusat)
        $startWeekNow = $now->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $endWeekNow = $now->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay();
        
        $liveKMeans = $this->kmeansService->calculateKMeansRealtime($startWeekNow, $endWeekNow);

        $barangTerlaris = collect($liveKMeans)->sortBy(function($item) {
            if ($item->label_cluster === 'Laris') return 1;
            if ($item->label_cluster === 'Sedang') return 2;
            return 3;
        })->values()->take(10)->map(function($item) {
            return [
                'kode' => $item->kode_barang,
                'nama' => $item->nama_barang,
                'stok' => $item->stok,
                'terjual' => $item->terjual,
                'label' => $item->label_cluster
            ];
        })->toArray();

        return view('dashboard.index', [
            'totalBarang' => $totalBarang, 'pemasukan' => $totalPemasukan, 'pengeluaran' => $totalPengeluaran, 'stokRendah' => $stokRendah,
            'chartPemasukan' => $chartPemasukan, 'chartPengeluaran' => $chartPengeluaran, 'chartLabels' => $chartLabels, 'filter' => $filter,
            'pendapatanBarang' => $pendapatanBarang, 'pendapatanJasa' => $pendapatanJasa, 'detailBarangPemasukan' => $detailBarangPemasukan,
            'detailJasaPemasukan' => $detailJasaPemasukan, 'barangTerlaris' => $barangTerlaris
        ]);
    }

    public function clusteringDetail(Request $request)
    {
        Carbon::setLocale('id');
        date_default_timezone_set('Asia/Jakarta');

        $user = Auth::user();
        $adminId = ($user->role === 'admin') ? $user->id : $user->admin_id;

        $now = Carbon::now();
        $bulanFilter = $request->input('bulan', $now->format('Y-m'));
        $mingguFilter = $request->input('minggu', 'Minggu 1');
        $labelFilter = $request->input('label_cluster', 'all');

        $parts = explode('-', $bulanFilter);
        $year = intval($parts[0]);
        $month = intval($parts[1]);

        $weekNumber = intval(filter_var($mingguFilter, FILTER_SANITIZE_NUMBER_INT));
        if ($weekNumber < 1) $weekNumber = 1;
        if ($weekNumber > 5) $weekNumber = 5;

        // Logika Kalender Riil
        $startOfMonth = Carbon::createFromDate($year, $month, 1);
        $startDate = $startOfMonth->copy()->startOfMonth()->subDay();
        for ($i = 1; $i <= $weekNumber; $i++) {
            $startDate = $startDate->addWeek()->startOfWeek(Carbon::MONDAY);
        }
        $endDate = $startDate->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay();

        if ($startDate->month !== $month) $startDate = $startOfMonth->copy()->startOfMonth()->startOfDay();
        if ($endDate->month !== $month) $endDate = $startOfMonth->copy()->endOfMonth()->endOfDay();
        
        $stringPeriodeTarget = $mingguFilter . ' Bulan ' . $startOfMonth->translatedFormat('F Y');

        $firstTransaksi = Transaksi::whereHas('user', function($q) use ($adminId) {
                $q->where('id', $adminId)->orWhere('admin_id', $adminId);
            })->orderBy('created_at', 'asc')->first();
            
        $startYear = $firstTransaksi ? Carbon::parse($firstTransaksi->created_at)->year : ($now->year - 1);
        
        $opsiPeriodeBulan = [];
        for ($y = $now->year; $y >= $startYear; $y--) {
            $maxMonth = ($y === $now->year) ? $now->month : 12;
            for ($m = $maxMonth; $m >= 1; $m--) {
                $opsiPeriodeBulan[] = sprintf('%04d-%02d', $y, $m);
            }
        }

        $records = collect();
        $formatStart = Carbon::parse($startDate)->translatedFormat('d F Y');
        $formatEnd = Carbon::parse($endDate)->translatedFormat('d F Y');
        $periodeStringDb = $formatStart . ' s/d ' . $formatEnd;

        // Panggil K-Means Terpusat
        $liveKMeans = $this->kmeansService->calculateKMeansRealtime($startDate, $endDate);
        
        // Mengunci query riwayat clustering database berdasarkan user_id Admin Bengkel
        $recordsDb = RiwayatClustering::with('barang')
            ->where('user_id', $adminId)
            ->where('periode', $periodeStringDb)
            ->get();

        if ($recordsDb->isNotEmpty()) {
            foreach ($recordsDb as $rec) {
                $records->push((object) [
                    'kode_barang' => $rec->kode_barang,
                    'nama_barang_live' => $rec->barang->nama_barang ?? 'Produk Dihapus',
                    'nilai_x_stok' => $rec->nilai_x_stok,
                    'nilai_y_terjual' => $rec->nilai_y_terjual,
                    'label_cluster' => $rec->label_cluster
                ]);
            }
        } else {
            foreach ($liveKMeans as $ld) {
                // Saring riwayat snapshot stok khusus bengkel ini
                $historiStok = DB::table('histori_stok')
                    ->where('user_id', $adminId)
                    ->where('barang_id', $ld->id_barang)
                    ->where('created_at', '<=', $endDate)
                    ->orderBy('created_at', 'desc')
                    ->value('sisa_stok_saat_ini');

                $stokTerKunci = $historiStok !== null ? (int)$historiStok : $ld->stok;

                $records->push((object) [
                    'kode_barang' => $ld->kode_barang,
                    'nama_barang_live' => $ld->nama_barang,
                    'nilai_x_stok' => $stokTerKunci,
                    'nilai_y_terjual' => $ld->terjual,
                    'label_cluster' => $ld->label_cluster
                ]);
            }
        }

        // ==========================================
        // FITUR URUTAN MULTI-LEVEL KATEGORI CLUSTER
        // ==========================================
        $records = $records->sort(function($a, $b) {
            $order = ['Laris' => 1, 'Sedang' => 2, 'Kurang Laris' => 3];
            $aOrder = $order[$a->label_cluster] ?? 4;
            $bOrder = $order[$b->label_cluster] ?? 4;

            if ($aOrder === $bOrder) {
                if ($a->nilai_y_terjual === $b->nilai_y_terjual) {
                    return $b->nilai_x_stok <=> $a->nilai_x_stok;
                }
                return $b->nilai_y_terjual <=> $a->nilai_y_terjual;
            }
            return $aOrder <=> $bOrder;
        })->values();

        if ($labelFilter !== 'all') {
            $records = $records->where('label_cluster', $labelFilter);
        }

        return view('dashboard.clustering_detail', compact(
            'records', 
            'bulanFilter', 
            'mingguFilter', 
            'labelFilter', 
            'opsiPeriodeBulan', 
            'stringPeriodeTarget'
        ));
    }
}