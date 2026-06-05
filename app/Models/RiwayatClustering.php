<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiwayatClustering extends Model
{
    use HasFactory;

    // Arahkan ke nama tabel yang benar
    protected $table = 'riwayat_clustering';

    // Sesuaikan primary key karena kita custom namanya
    protected $primaryKey = 'id_riwayat';

    // Daftarkan kolom yang boleh diisi (Mass Assignment)
    protected $fillable = [
        'tanggal_proses',
        'periode',
        'kode_barang',
        'nilai_x_stok',
        'nilai_y_terjual',
        'label_cluster'
    ];

    // Relasi balik (BelongsTo) ke tabel Barang
    public function barang()
    {
        // Parameter: (NamaModelTarget, foreign_key_di_tabel_ini, primary_key_di_tabel_target)
        return $this->belongsTo(Barang::class, 'kode_barang', 'kode_barang');
    }
}