<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    protected $table = 'barang';

    protected $fillable = [
        'user_id',
        'kode_barang',
        'nama_barang',
        'stok',
        'satuan',
        'harga_beli',
        'harga_jual',
        'kategori',
        'supplier',
        'tipe_barang'
    ];

    public function barang()
    {
        return $this->hasMany(Barang::class);
    }
}