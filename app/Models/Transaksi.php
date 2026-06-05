<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    use HasFactory;

    protected $table = 'transaksi';

    protected $fillable = [
        'no_invoice',
        'user_id',
        'jenis_motor',
        'biaya_jasa_servis',
        'total_harga',
        'metode_pembayaran',
        'status',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIP
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Ubah nama method ini menjadi "details" agar dikenali oleh Controller
    public function details()
    {
        return $this->hasMany(DetailTransaksi::class, 'transaksi_id');
    }
}