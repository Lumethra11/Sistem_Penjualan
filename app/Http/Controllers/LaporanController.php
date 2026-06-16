<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $adminId = $user->role === 'admin' ? $user->id : $user->admin_id;

        // Dropdown filter kasir konsisten
        $daftarKasir = User::where('admin_id', $adminId)->get();

        // Tangkap request filter URL
        $jenisLaporan = $request->input('jenis_laporan', 'penjualan');
        $tglMulai = $request->input('tgl_mulai');
        $tglSelesai = $request->input('tgl_selesai');
        $tipeBarang = $request->input('tipe_barang', 'all'); 
        $kasirId = $request->input('kasir_id');

        $results = collect();

        // =========================================================================
        // 1. DESAIN LOGIKA: LAPORAN PENJUALAN KESELURUHAN
        // =========================================================================
        if ($jenisLaporan === 'penjualan') {
            $query = DetailTransaksi::with(['transaksi.user', 'barang'])
                ->whereHas('transaksi', function($q) use ($adminId, $tglMulai, $tglSelesai, $kasirId) {
                    $q->where('status', 'selesai')
                      ->whereHas('user', function($qu) use ($adminId) {
                          $qu->where('id', $adminId)->orWhere('admin_id', $adminId);
                      });

                    if ($tglMulai) { $q->whereDate('created_at', '>=', $tglMulai); }
                    if ($tglSelesai) { $q->whereDate('created_at', '<=', $tglSelesai); }
                    if ($kasirId) { $q->where('user_id', $kasirId); }
                });

            $dataDetail = $query->get();

            // Transformasi data menggunakan Accessor Pintar Model yang baru kita buat
            $results = $dataDetail->map(function($item) {
                return (object) [
                    'tanggal'   => $item->transaksi->created_at,
                    'invoice'   => $item->transaksi->no_invoice,
                    'kode'      => $item->kode_produk, // Mengambil properti virtual model
                    'nama'      => $item->nama_produk, // Mengambil properti virtual model
                    'qty'       => $item->jumlah,
                    'harga'     => $item->harga_jual_satuan,
                    'total'     => $item->subtotal,
                    'operator'  => $item->transaksi->user->name ?? 'Kasir'
                ];
            });
        } 
        
        // =========================================================================
        // 2. DESAIN LOGIKA: LAPORAN BARANG MASUK (GABUNGAN PENDAFTARAN & HISTORI)
        // =========================================================================
        elseif ($jenisLaporan === 'barang_masuk') {
            // Sumber A: Dari mutasi pencatatan histori_stok
            $queryHistori = DB::table('histori_stok')
                ->join('barang', 'histori_stok.barang_id', '=', 'barang.id')
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

            // Sumber B: Dari entri awal record pembuatan data di master barang
            $queryBarang = DB::table('barang');
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

            // Dimerge menjadi satu koleksi data utuh agar 20+ data barang masuk Anda tampil sempurna
            $results = $resultsHistori->merge($resultsBarang)->sortByDesc('tanggal')->values();
        } 
        
        // =========================================================================
        // 3. DESAIN LOGIKA: LAPORAN BARANG KELUAR
        // =========================================================================
        else {
            $query = DB::table('histori_stok')
                ->join('barang', 'histori_stok.barang_id', '=', 'barang.id')
                ->leftJoin('detail_transaksi', 'barang.id', '=', 'detail_transaksi.barang_id')
                ->leftJoin('transaksi', 'detail_transaksi.transaksi_id', '=', 'transaksi.id')
                ->leftJoin('users', 'transaksi.user_id', '=', 'users.id')
                ->where('histori_stok.jenis_pergerakan', 'keluar');

            if ($tglMulai) { $query->whereDate('histori_stok.created_at', '>=', $tglMulai); }
            if ($tglSelesai) { $query->whereDate('histori_stok.created_at', '<=', $tglSelesai); }
            if ($kasirId) { $query->where('transaksi.user_id', $kasirId); }

            $results = $query->select(
                'histori_stok.created_at as tanggal',
                'barang.kode_barang as kode',
                'barang.nama_barang as nama',
                'histori_stok.jumlah as qty',
                'barang.harga_jual as harga',
                DB::raw('(histori_stok.jumlah * barang.harga_jual) as total'),
                DB::raw('COALESCE(users.name, "Sistem") as operator')
            )->orderBy('histori_stok.created_at', 'desc')->distinct()->get();
        }

        return view('laporan.index', compact('results', 'daftarKasir', 'jenisLaporan', 'tglMulai', 'tglSelesai', 'tipeBarang', 'kasirId'));
    }
}