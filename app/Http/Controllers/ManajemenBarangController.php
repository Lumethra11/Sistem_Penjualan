<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use Illuminate\Http\Request;
use App\Imports\BarangImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class ManajemenBarangController extends Controller
{
    // DATA BARANG + SEARCH + FILTER TIPE + PAGINATION
    public function dataBarang(Request $request)
    {
        $search = $request->input('search');
        $tipe = $request->input('tipe', 'stok'); 
        
        $query = Barang::where('tipe_barang', $tipe)->latest();

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama_barang', 'like', '%' . $search . '%')
                  ->orWhere('kode_barang', 'like', '%' . $search . '%')
                  ->orWhere('kategori', 'like', '%' . $search . '%');
            });
        }

        $barang = $query->paginate(20)->appends($request->all());

        return view('manajemenbarang.databarang', compact('barang', 'search', 'tipe'));
    }

    // FORM BARANG
    public function formBarang()
    {
        return view('manajemenbarang.formbarang');
    }

    // API PENCARIAN REKOMENDASI AUTOCOMPLETE
    public function cariRekomendasi(Request $request)
    {
        $term = $request->input('term');
        
        if (empty($term)) {
            return response()->json([]);
        }

        // Mengambil data barang yang mirip dengan inputan kasir/admin
        $barang = Barang::where('nama_barang', 'like', '%' . $term . '%')
            ->limit(10)
            ->get(['id', 'nama_barang', 'tipe_barang', 'satuan', 'harga_beli', 'harga_jual', 'kategori', 'supplier']);

        return response()->json($barang);
    }

    // GENERATE KODE BARANG
    private function generateKodeBarang($kategori)
    {
        $cleanKategori = preg_replace('/[^A-Za-z]/', '', $kategori);
        $prefix = strtoupper(substr($cleanKategori, 0, 3));
        $prefix = str_pad($prefix, 3, 'X');

        $lastBarang = Barang::where('kode_barang', 'like', $prefix . '%')
            ->orderBy('kode_barang', 'desc')
            ->first();

        if ($lastBarang) {
            $lastNumber = intval(substr($lastBarang->kode_barang, 3));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        $formattedNumber = str_pad($newNumber, 3, '0', STR_PAD_LEFT);
        return $prefix . $formattedNumber;
    }

    // STORE BARANG (LOGIKA ACCUMULATION JIKA NAMA & TIPE SAMA)
    public function store(Request $request)
    {
        $request->validate([
            'nama_barang' => 'required|string|max:255',
            'tipe_barang' => 'required|in:stok,non_stok',
            'stok'        => 'required|integer|min:0',
            'satuan'      => 'required|string|max:100',
            'harga_beli'  => 'required|integer|min:0',
            'harga_jual'  => 'required|integer|min:0',
            'kategori'    => 'required|string|max:255',
            'supplier'    => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            $barangEksis = Barang::where('nama_barang', $request->nama_barang)
                ->where('tipe_barang', $request->tipe_barang)
                ->first();

            if ($barangEksis) {
                $stokLama = $barangEksis->stok;
                $tambahStok = intval($request->stok);
                $stokBaru = $stokLama + $tambahStok;

                $barangEksis->update([
                    'stok' => $stokBaru,
                    'harga_beli' => $request->harga_beli,
                    'harga_jual' => $request->harga_jual,
                    'satuan' => $request->satuan,
                    'kategori' => $request->kategori,
                    'supplier' => $request->supplier,
                ]);

                DB::table('histori_stok')->insert([
                    'barang_id' => $barangEksis->id,
                    'jenis_pergerakan' => 'masuk',
                    'jumlah' => $tambahStok,
                    'sisa_stok_saat_ini' => $stokBaru,
                    'keterangan' => 'Restok barang (akumulasi nama sama) via Form Master Barang',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                DB::commit();
                return redirect()
                    ->route('manajemenbarang.databarang', ['tipe' => $request->tipe_barang])
                    ->with('success', 'Stok barang berhasil diakumulasikan ke data lama.');
            }

            $kodeBarang = $this->generateKodeBarang($request->kategori);

            $barang = Barang::create([
                'kode_barang' => $kodeBarang,
                'nama_barang' => $request->nama_barang,
                'tipe_barang' => $request->tipe_barang,
                'stok'        => $request->stok,
                'satuan'      => $request->satuan,
                'harga_beli'  => $request->harga_beli,
                'harga_jual'  => $request->harga_jual,
                'kategori'    => $request->kategori,
                'supplier'    => $request->supplier,
            ]);

            DB::table('histori_stok')->insert([
                'barang_id' => $barang->id,
                'jenis_pergerakan' => 'masuk',
                'jumlah' => $request->stok,
                'sisa_stok_saat_ini' => $request->stok,
                'keterangan' => 'Pendaftaran data master barang baru',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::commit();
            return redirect()
                ->route('manajemenbarang.databarang', ['tipe' => $request->tipe_barang])
                ->with('success', 'Barang baru berhasil ditambahkan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // UPDATE BARANG STOK
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_barang' => 'required|string|max:255',
            'stok'        => 'required|integer|min:0',
            'satuan'      => 'required|string|max:100',
            'harga_beli'  => 'required|integer|min:0',
            'harga_jual'  => 'required|integer|min:0',
            'kategori'    => 'required|string|max:255',
            'supplier'    => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $barang = Barang::findOrFail($id);
            $stokLama = $barang->stok;
            $stokBaru = intval($request->stok);

            $barang->update([
                'nama_barang' => $request->nama_barang,
                'stok'        => $stokBaru,
                'satuan'      => $request->satuan,
                'harga_beli'  => $request->harga_beli,
                'harga_jual'  => $request->harga_jual,
                'kategori'    => $request->kategori,
                'supplier'    => $request->supplier,
            ]);

            if ($stokLama != $stokBaru) {
                $selisih = $stokBaru - $stokLama;
                DB::table('histori_stok')->insert([
                    'barang_id' => $barang->id,
                    'jenis_pergerakan' => 'penyesuaian',
                    'jumlah' => abs($selisih),
                    'sisa_stok_saat_ini' => $stokBaru,
                    'keterangan' => 'Penyesuaian manual data barang oleh admin',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            DB::commit();
            return redirect()
                ->route('manajemenbarang.databarang', ['tipe' => 'stok'])
                ->with('success', 'Data barang berhasil diperbarui!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal update barang: ' . $e->getMessage());
        }
    }

    // PINDAH KELOMPOK BARANG (NON-STOK KE STOK)
    public function pindahKeStok($id)
    {
        DB::beginTransaction();
        try {
            $barang = Barang::findOrFail($id);

            $barang->update([
                'tipe_barang' => 'stok',
                'stok' => 0 
            ]);

            DB::table('histori_stok')->insert([
                'barang_id' => $barang->id,
                'jenis_pergerakan' => 'penyesuaian',
                'jumlah' => 0,
                'sisa_stok_saat_ini' => 0,
                'keterangan' => 'Migrasi tipe barang dari Non-Stok menjadi tipe Barang Stok',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::commit();
            return redirect()
                ->route('manajemenbarang.databarang', ['tipe' => 'stok'])
                ->with('success', "Barang '{$barang->nama_barang}' sukses dipindahkan ke kelompok Barang Stok.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memindahkan tipe barang: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $barang = Barang::findOrFail($id);
        $tipe = $barang->tipe_barang;
        $barang->delete();

        return redirect()
            ->route('manajemenbarang.databarang', ['tipe' => $tipe])
            ->with('success', 'Barang berhasil dihapus');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        Excel::import(new BarangImport, $request->file('file'));

        return redirect()
            ->route('manajemenbarang.databarang')
            ->with('success', 'Import barang berhasil');
    }

    public function downloadTemplate()
    {
        $path = public_path('template/template_barang.xlsx');
        return response()->download($path);
    }
}