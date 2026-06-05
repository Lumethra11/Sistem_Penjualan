<?php

namespace App\Imports;

use App\Models\Barang;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class BarangImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        unset($rows[0]); // hapus header

        foreach ($rows as $row) {
            if (!$row || count($row) < 7) {
                continue; // skip kalau row tidak lengkap
            }

            $namaBarang = $row[0];
            $stok       = $row[1];
            $satuan     = $row[2];
            $hargaBeli  = $row[3];
            $hargaJual  = $row[4];
            $kategori   = $row[5];
            $supplier   = $row[6] ?? '-';
            
            // Format tipe barang: ubah ke lowercase, ganti spasi/- menjadi underscore
            $tipeRaw    = strtolower(trim($row[7] ?? 'stok'));
            $tipeBarang = ($tipeRaw === 'non-stok' || $tipeRaw === 'non_stok' || $tipeRaw === 'non stok') ? 'non_stok' : 'stok';

            if (!$kategori) continue;

            $prefix = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $kategori), 0, 3));

            $lastBarang = Barang::where('kode_barang', 'like', $prefix . '%')
                ->orderBy('kode_barang', 'desc')
                ->first();

            if ($lastBarang) {
                $lastNumber = intval(substr($lastBarang->kode_barang, 3));
                $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
            } else {
                $newNumber = '001';
            }

            $kodeBarang = $prefix . $newNumber;

            Barang::create([
                'kode_barang' => $kodeBarang,
                'nama_barang' => $namaBarang,
                'tipe_barang' => $tipeBarang,
                'stok'        => $stok,
                'satuan'      => $satuan,
                'harga_beli'  => $hargaBeli,
                'harga_jual'  => $hargaJual,
                'kategori'    => $kategori,
                'supplier'    => $supplier,
            ]);
        }
    }
}