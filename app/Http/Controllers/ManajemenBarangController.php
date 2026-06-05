<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use Illuminate\Http\Request;
use App\Imports\BarangImport;
use Maatwebsite\Excel\Facades\Excel;

class ManajemenBarangController extends Controller
{
    // DATA BARANG + SEARCH + FILTER TIPE + PAGINATION
    public function dataBarang(Request $request)
    {
        $search = $request->input('search');
        $tipe = $request->input('tipe', 'stok'); // Default tampilkan barang stok
        
        // Filter berdasarkan tipe barang terlebih dahulu
        $query = Barang::where('tipe_barang', $tipe)->latest();

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama_barang', 'like', '%' . $search . '%')
                  ->orWhere('kode_barang', 'like', '%' . $search . '%')
                  ->orWhere('kategori', 'like', '%' . $search . '%');
            });
        }

        // Paginasi 20 data per halaman dan bawa semua parameter URL
        $barang = $query->paginate(20)->appends($request->all());

        return view('manajemenbarang.databarang', compact('barang', 'search', 'tipe'));
    }

    // FORM BARANG
    public function formBarang()
    {
        return view('manajemenbarang.formbarang');
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

    // STORE BARANG
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

        $kodeBarang = $this->generateKodeBarang($request->kategori);

        Barang::create([
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

        return redirect()
            ->route('manajemenbarang.databarang', ['tipe' => $request->tipe_barang])
            ->with('success', 'Barang berhasil ditambahkan');
    }

    // UPDATE BARANG
    public function update(Request $request, $id)
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

        $barang = Barang::findOrFail($id);

        $barang->update([
            'nama_barang' => $request->nama_barang,
            'tipe_barang' => $request->tipe_barang,
            'stok'        => $request->stok,
            'satuan'      => $request->satuan,
            'harga_beli'  => $request->harga_beli,
            'harga_jual'  => $request->harga_jual,
            'kategori'    => $request->kategori,
            'supplier'    => $request->supplier,
        ]);

        return redirect()
            ->route('manajemenbarang.databarang', ['tipe' => $request->tipe_barang])
            ->with('success', 'Data barang berhasil diperbarui!');
    }
    
    // MENGHAPUS BARANG
    public function destroy($id)
    {
        $barang = Barang::findOrFail($id);
        $tipe = $barang->tipe_barang;
        $barang->delete();

        return redirect()
            ->route('manajemenbarang.databarang', ['tipe' => $tipe])
            ->with('success', 'Barang berhasil dihapus');
    }

    // IMPORT EXCEL
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

    // DOWNLOAD TEMPLATE
    public function downloadTemplate()
    {
        $path = public_path('template/template_barang.xlsx');
        return response()->download($path);
    }
}