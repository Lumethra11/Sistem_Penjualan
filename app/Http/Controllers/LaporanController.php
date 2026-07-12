<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanController extends Controller
{
    /**
     * Fungsi privat untuk mengambil data laporan berdasarkan filter request.
     * Dikunci menggunakan relasi Tenant/Bengkel Utama (Secure Multi-tenant).
     */
    private function getLaporanData(Request $request)
    {
        $user = Auth::user();
        // STRATEGI JANGKAR MULTI-TENANT: Mengunci hak kepemilikan data pada ID Admin (Pemilik Bengkel)
        $adminId = ($user->role === 'admin') ? $user->id : $user->admin_id;

        $jenisLaporan = $request->input('jenis_laporan', 'penjualan');
        $tglMulai = $request->input('tgl_mulai');
        $tglSelesai = $request->input('tgl_selesai');
        $tipeBarang = $request->input('tipe_barang', 'all'); 
        $kasirId = $request->input('kasir_id');

        $results = collect();

        // 1. LOGIKA: LAPORAN PENJUALAN KESELURUHAN (Terfilter per Ruang Lingkup Bengkel)
        if ($jenisLaporan === 'penjualan') {
            $query = DetailTransaksi::with(['transaksi.user', 'barang'])
                ->whereHas('transaksi', function($q) use ($adminId, $tglMulai, $tglSelesai, $kasirId) {
                    $q->where('status', 'selesai')
                      ->whereHas('user', function($qu) use ($adminId) {
                          // Mengamankan transaksi: Harus milik admin ini atau kasir bawahan admin ini
                          $qu->where('id', $adminId)->orWhere('admin_id', $adminId);
                      });

                    if ($tglMulai) { $q->whereDate('created_at', '>=', $tglMulai); }
                    if ($tglSelesai) { $q->whereDate('created_at', '<=', $tglSelesai); }
                    // Jika ada filter spesifik kasir dari form laporan
                    if ($kasirId) { $q->where('user_id', $kasirId); }
                });

            $dataDetail = $query->get();

            $results = $dataDetail->map(function($item) {
                return (object) [
                    'tanggal'   => $item->transaksi->created_at,
                    'invoice'   => $item->transaksi->no_invoice,
                    'kode'      => $item->kode_produk, 
                    'nama'      => $item->nama_produk, 
                    'qty'       => $item->jumlah,
                    'harga'     => $item->harga_jual_satuan,
                    'total'     => $item->subtotal,
                    'operator'  => $item->transaksi->user->name ?? 'Kasir'
                ];
            });
        } 
        
        // 2. LOGIKA: LAPORAN BARANG MASUK (Terfilter per Ruang Lingkup Bengkel)
        elseif ($jenisLaporan === 'barang_masuk') {
            // Ambil ID semua barang yang dimiliki oleh Bengkel (Admin) ini
            $barangIds = Barang::where('user_id', $adminId)->pluck('id');

            // Sumber A: Dari catatan mutasi histori_stok (Hanya mengambil data dari barang milik bengkel ini)
            $queryHistori = DB::table('histori_stok')
                ->join('barang', 'histori_stok.barang_id', '=', 'barang.id')
                ->whereIn('histori_stok.barang_id', $barangIds)
                ->where('histori_stok.jenis_pergerakan', 'masuk');

            if ($tglMulai) { $queryHistori->whereDate('histori_stok.created_at', '>=', $tglMulai); }
            if ($tglSelesai) { $queryHistori->whereDate('histori_stok.created_at', '<=', $tglSelesai); }
            if ($tipeBarang !== 'all') { $queryHistori->where('barang.tipe_barang', $tipeBarang); }

            $resultsHistori = $queryHistori->select(
                'histori_stok.created_at as tanggal',
                'barang.kode_barang as kode',
                'barang.nama_barang as nama',
                'histori_stok.jumlah as qty',
                'barang.harga_beli as harga',
                DB::raw('(histori_stok.jumlah * barang.harga_beli) as total'),
                DB::raw('"Sistem/Admin" as operator')
            )->get();

            // Sumber B: Dari entri awal record pembuatan data di master barang milik bengkel ini
            $queryBarang = DB::table('barang')->where('user_id', $adminId);
            if ($tglMulai) { $queryBarang->whereDate('created_at', '>=', $tglMulai); }
            if ($tglSelesai) { $queryBarang->whereDate('created_at', '<=', $tglSelesai); }
            if ($tipeBarang !== 'all') { $queryBarang->where('tipe_barang', $tipeBarang); }

            $resultsBarang = $queryBarang->select(
                'created_at as tanggal',
                'kode_barang as kode',
                'nama_barang as nama',
                'stok as qty',
                'harga_beli as harga',
                DB::raw('(stok * harga_beli) as total'),
                DB::raw('"Admin (Awal)" as operator')
            )->get();

            // Gabungkan record histori restok dengan data saldo awal pendaftaran barang
            $results = $resultsHistori->merge($resultsBarang)->sortByDesc('tanggal')->values();
        }

        return $results;
    }

    /**
     * TAMPILAN DASHBOARD LAPORAN
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $adminId = ($user->role === 'admin') ? $user->id : $user->admin_id;

        // Daftar pilihan dropdown kasir dibatasi hanya yang bekerja di bawah struktur admin ini
        $daftarKasir = User::where('admin_id', $adminId)->get();

        $jenisLaporan = $request->input('jenis_laporan', 'penjualan');
        $tglMulai = $request->input('tgl_mulai');
        $tglSelesai = $request->input('tgl_selesai');
        $tipeBarang = $request->input('tipe_barang', 'all'); 
        $kasirId = $request->input('kasir_id');

        $results = $this->getLaporanData($request);

        return view('laporan.index', compact(
            'results', 
            'daftarKasir', 
            'jenisLaporan', 
            'tglMulai', 
            'tglSelesai', 
            'tipeBarang', 
            'kasirId'
        ));
    }

    /**
     * EXPORT EXCEL VIA BROWSER STREAM
     */
    public function exportExcel(Request $request)
    {
        $jenisLaporan = $request->input('jenis_laporan', 'penjualan');
        $results = $this->getLaporanData($request);
        $filename = "Laporan_" . $jenisLaporan . "_" . date('YmdHis') . ".xls";

        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Pragma: no-cache");
        header("Expires: 0");

        return view('laporan.export_excel', compact('results', 'jenisLaporan'));
    }

    /**
     * EXPORT PDF VIA DOMPDF
     */
    public function exportPdf(Request $request)
    {
        $jenisLaporan = $request->input('jenis_laporan', 'penjualan');
        $results = $this->getLaporanData($request);
        
        $pdf = Pdf::loadView('laporan.export_pdf', compact('results', 'jenisLaporan'));
        $filename = "Laporan_" . $jenisLaporan . "_" . date('YmdHis') . ".pdf";
        
        return $pdf->download($filename);
    }
}