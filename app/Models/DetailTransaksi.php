<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailTransaksi extends Model
{
    use HasFactory;

    protected $table = 'detail_transaksi';

    protected $fillable = [
        'transaksi_id',
        'barang_id',
        'nama_barang_manual',
        'harga_modal',
        'harga_jual_satuan',
        'jumlah',
        'subtotal',
    ];

    /*
    |--------------------------------------------------------------------------
    | ACCESSOR PINTAR (Mencegah Nama Barang Manual Kasir Hilang)
    |--------------------------------------------------------------------------
    */
    // Panggil Atribut ini di Blade/Controller dengan cara: $detail->nama_produk
    public function getNamaProdukAttribute()
    {
        if ($this->barang_id && $this->barang) {
            return $this->barang->nama_barang;
        }
        
        return $this->nama_barang_manual ?? '-';
    }

    // Panggil Atribut ini untuk Kode Barang dengan cara: $detail->kode_produk
    public function getKodeProdukAttribute()
    {
        if ($this->barang_id && $this->barang) {
            return $this->barang->kode_barang;
        }
        
        return 'MANUAL';
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIP
    |--------------------------------------------------------------------------
    */
    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class, 'transaksi_id');
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }
}