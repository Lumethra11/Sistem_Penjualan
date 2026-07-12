<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notifikasi extends Model
{
    use HasFactory;

    protected $table = 'notifikasi';

    protected $fillable = [
        'user_id',
        'id_item',
        'tipe',
        'icon',
        'judul',
        'pesan',
        'is_read'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}