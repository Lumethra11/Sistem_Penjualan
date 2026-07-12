<?php

namespace App\Imports;

use App\Models\Barang;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class BarangImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        // 1. Hapus baris header (indeks 0)
        unset($rows[0]); 

        // 2. Cek apakah setelah header dihapus, datanya benar-benar kosong
        if ($rows->isEmpty() || $rows->filter(function($row) { return !empty($row[0]); })->isEmpty()) {
            throw new \Exception('File Excel kosong atau tidak berisi data barang yang valid.');
        }

        foreach ($rows as $row) {
            // Skip kalau row tidak memiliki kolom yang cukup
            if (!$row || count($row) < 7) {
                continue; 
            }

            $namaBarang = $row[0];
            $stok       = $row[1];
            $satuan     = $row[2];
            $hargaBeli  = $row[3];
            $hargaJual  = $row[4];
            $kategori   = $row[5];
            $supplier   = $row[6] ?? '-';
            
            // Skip jika data nama_barang atau kategori ternyata kosong di baris ini
            if (empty(trim($namaBarang)) || empty(trim($kategori))) {
                continue;
            }

            // Format tipe barang
            $tipeRaw    = strtolower(trim($row[7] ?? 'stok'));
            $tipeBarang = ($tipeRaw === 'non-stok' || $tipeRaw === 'non_stok' || $tipeRaw === 'non stok') ? 'non_stok' : 'stok';

            $prefix = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $kategori), 0, 3));
            $prefix = str_pad($prefix, 3, 'X'); 

            // FIX 1: Cari kode barang terakhir HANYA milik user/bengkel yang sedang login
            $lastBarang = Barang::where('user_id', auth()->id())
                ->where('kode_barang', 'like', $prefix . '%')
                ->orderBy('kode_barang', 'desc')
                ->first();

            if ($lastBarang) {
                $lastNumber = intval(substr($lastBarang->kode_barang, 3));
                $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
            } else {
                $newNumber = '001';
            }

            $kodeBarang = $prefix . $newNumber;

            // FIX 2: Masukkan user_id ke dalam array creation data
            Barang::create([
                'user_id'     => auth()->id(), // Mengunci kepemilikan data barang
                'kode_barang' => $kodeBarang,
                'nama_barang' => $namaBarang,
                'tipe_barang' => $tipeBarang,
                'stok'        => intval($stok),
                'satuan'      => $satuan,
                'harga_beli'  => intval($hargaBeli),
                'harga_jual'  => intval($hargaJual),
                'kategori'    => $kategori,
                'supplier'    => $supplier,
            ]);
        }
    }
}