<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('filter', 'hari');
        $now = Carbon::now();

        // 1. Total Ragam Jenis Barang Global
        $totalBarang = Barang::count();

        // 2. Query Utama untuk Card Indikator (Tergantung Filter Dropdown)
        $queryPemasukan = Transaksi::where('status', 'selesai');
        $queryPengeluaran = DetailTransaksi::whereHas('transaksi', function ($q) {
            $q->where('status', 'selesai');
        });

        if ($filter === 'hari') {
            $queryPemasukan->whereDate('created_at', Carbon::today());
            $queryPengeluaran->whereHas('transaksi', function ($q) {
                $q->whereDate('created_at', Carbon::today());
            });
        } elseif ($filter === 'minggu') {
            $startOfWeek = Carbon::now()->startOfWeek();
            $endOfWeek = Carbon::now()->endOfWeek();
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

        // 3. Stok Rendah
        $stokRendah = Barang::where('tipe_barang', 'stok')->where('stok', '<=', 10)->count();

        // =========================================================
        // 4. LOGIKA GRAFIK DINAMIS (SUMBU X MENGIKUTI FILTER)
        // =========================================================
        $chartLabels = [];
        $chartPemasukan = [];
        $chartPengeluaran = [];

        if ($filter === 'hari') {
            // Urutan Sumbu X berdasarkan jam operasional bisnis bengkel
            $chartLabels = ['08:00', '10:00', '12:00', '14:00', '16:00', '17:00+'];
            
            // Loop data per 2 jam hari ini
            $hoursRange = [
                ['00:00:00', '09:59:59'],
                ['10:00:00', '11:59:59'],
                ['12:00:00', '13:59:59'],
                ['14:00:00', '15:59:59'],
                ['16:00:00', '16:59:59'],
                ['17:00:00', '23:59:59']
            ];

            foreach ($hoursRange as $range) {
                // Hitung pemasukan per jam
                $chartPemasukan[] = Transaksi::where('status', 'selesai')
                    ->whereDate('created_at', Carbon::today())
                    ->whereTime('created_at', '>=', $range[0])
                    ->whereTime('created_at', '<=', $range[1])
                    ->sum('total_harga');

                // Hitung pengeluaran modal per jam
                $detailsHour = DetailTransaksi::whereHas('transaksi', function($q) use ($range) {
                        $q->where('status', 'selesai')->whereDate('created_at', Carbon::today())
                          ->whereTime('created_at', '>=', $range[0])->whereTime('created_at', '<=', $range[1]);
                    })->with('barang')->get();

                $modalHourSum = 0;
                foreach ($detailsHour as $dh) {
                    $itemModal = $dh->harga_modal > 0 ? $dh->harga_modal : ($dh->barang ? $dh->barang->harga_beli : 0);
                    $modalHourSum += ($itemModal * $dh->jumlah);
                }
                $chartPengeluaran[] = $modalHourSum;
            }

        } elseif ($filter === 'minggu') {
            // Urutan Sumbu X berdasarkan nama hari dalam seminggu berjalan
            $chartLabels = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
            $mapDays = [2, 3, 4, 5, 6, 7, 1]; // Index MySQL DAYOFWEEK

            $startW = Carbon::now()->startOfWeek();
            $endW = Carbon::now()->endOfWeek();

            $pemasukanMingguan = Transaksi::where('status', 'selesai')
                ->whereBetween('created_at', [$startW, $endW])
                ->select(DB::raw('DAYOFWEEK(created_at) as hari'), DB::raw('SUM(total_harga) as total'))
                ->groupBy('hari')->get()->pluck('total', 'hari')->toArray();

            $pengeluaranRawMingguan = DetailTransaksi::whereHas('transaksi', function ($q) use ($startW, $endW) {
                    $q->where('status', 'selesai')->whereBetween('created_at', [$startW, $endW]);
                })
                ->with('barang')->join('transaksi', 'detail_transaksi.transaksi_id', '=', 'transaksi.id')
                ->select('detail_transaksi.*', 'transaksi.created_at as tx_date')->get();

            $pengeluaranMingguan = [];
            foreach ($pengeluaranRawMingguan as $prm) {
                $dayIndex = Carbon::parse($prm->tx_date)->dayOfWeek + 1;
                $modalHarga = $prm->harga_modal > 0 ? $prm->harga_modal : ($prm->barang ? $prm->barang->harga_beli : 0);
                $pengeluaranMingguan[$dayIndex] = ($pengeluaranMingguan[$dayIndex] ?? 0) + ($modalHarga * $prm->jumlah);
            }

            foreach ($mapDays as $day) {
                $chartPemasukan[] = $pemasukanMingguan[$day] ?? 0;
                $chartPengeluaran[] = $pengeluaranMingguan[$day] ?? 0;
            }

        } elseif ($filter === 'bulan') {
            // Urutan Sumbu X dipecah berdasarkan rentang minggu di bulan ini (Minggu 1 s/d Minggu 4/5)
            $chartLabels = ['Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4', 'Sisa Hari'];
            
            // Definisikan tanggal cut-off pemisah minggu di internal bulan berjalan
            $weeksRange = [
                [1, 7],   // Tgl 1 - 7
                [8, 14],  // Tgl 8 - 14
                [15, 21], // Tgl 15 - 21
                [22, 28], // Tgl 22 - 28
                [29, 31]  // Sisa akhir bulan
            ];

            foreach ($weeksRange as $wRange) {
                // Hitung pemasukan per minggu di bulan ini
                $chartPemasukan[] = Transaksi::where('status', 'selesai')
                    ->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)
                    ->whereDay('created_at', '>=', $wRange[0])->whereDay('created_at', '<=', $wRange[1])
                    ->sum('total_harga');

                // Hitung pengeluaran modal per minggu di bulan ini
                $detailsWeek = DetailTransaksi::whereHas('transaksi', function($q) use ($now, $wRange) {
                        $q->where('status', 'selesai')->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)
                          ->whereDay('created_at', '>=', $wRange[0])->whereDay('created_at', '<=', $wRange[1]);
                    })->with('barang')->get();

                $modalWeekSum = 0;
                foreach ($detailsWeek as $dw) {
                    $itemModal = $dw->harga_modal > 0 ? $dw->harga_modal : ($dw->barang ? $dw->barang->harga_beli : 0);
                    $modalWeekSum += ($itemModal * $dw->jumlah);
                }
                $chartPengeluaran[] = $modalWeekSum;
            }
        }

        // 5. Data Modal Detail Pop-up (Tetap Akurat Mengikuti Filter)
        $pendapatanJasa = (clone $queryPemasukan)->sum('biaya_jasa_servis');
        $pendapatanBarang = $totalPemasukan - $pendapatanJasa;

        $detailBarangPemasukan = (clone $queryPengeluaran)
            ->select('barang_id', 'nama_barang_manual', DB::raw('SUM(jumlah) as total_qty'), DB::raw('SUM(harga_jual_satuan * jumlah) as total_jual'))
            ->groupBy('barang_id', 'nama_barang_manual')->get();

        foreach ($detailBarangPemasukan as $item) {
            $item->nama = $item->barang_id ? ($item->barang ? $item->barang->nama_barang : '-') : $item->nama_barang_manual;
            $item->harga_beli_satuan = $item->barang_id ? ($item->barang ? $item->barang->harga_beli : 0) : 0;
            $item->total_modal = $item->harga_beli_satuan * $item->total_qty;
            $item->keuntungan = $item->total_jual - $item->total_modal;
        }

        $detailJasaPemasukan = (clone $queryPemasukan)->where('biaya_jasa_servis', '>', 0)
            ->select('jenis_motor', 'biaya_jasa_servis as nominal')->get();

        $barangTerlaris = [];

        return view('dashboard.index', [
            'totalBarang' => $totalBarang,
            'pemasukan' => $totalPemasukan,
            'pengeluaran' => $totalPengeluaran,
            'stokRendah' => $stokRendah,
            'chartPemasukan' => $chartPemasukan,
            'chartPengeluaran' => $chartPengeluaran,
            'chartLabels' => $chartLabels,
            'filter' => $filter,
            'pendapatanBarang' => $pendapatanBarang,
            'pendapatanJasa' => $pendapatanJasa,
            'detailBarangPemasukan' => $detailBarangPemasukan,
            'detailJasaPemasukan' => $detailJasaPemasukan,
            'barangTerlaris' => $barangTerlaris
        ]);
    }
}