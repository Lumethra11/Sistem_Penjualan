<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use Notifiable, SoftDeletes;

    protected $fillable = [
        'admin_id',
        'role',
        'name',
        'username',
        'email',
        'nama_toko',
        'no_telp',
        'foto_profil',
        'alamat',
        'password',
        'is_active',
        'can_input_manual_barang',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relasi
    |--------------------------------------------------------------------------
    */

    // Kasir -> Admin
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    // Admin -> Banyak Kasir
    public function kasirs()
    {
        return $this->hasMany(User::class, 'Admin_id');
    }
}