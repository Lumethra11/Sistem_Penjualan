<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function index()
    {
        return view('profile.index');
    }

    public function edit()
    {
        return view('profile.edit');
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        // 1. STRATEGI VALIDASI BERDASARKAN HAK AKSES ROLE USER
        $rules = [
            'name'        => 'required|max:255',
            'username'    => 'required|max:255|unique:users,username,' . $user->id,
            'email'       => 'required|email|unique:users,email,' . $user->id,
            'no_telp'     => 'nullable|max:255',
            'foto_profil' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ];

        // Hanya validasi field Toko dan Alamat jika user yang login adalah ADMIN
        if ($user->role === 'admin') {
            $rules['nama_toko'] = 'nullable|max:255';
            $rules['alamat']    = 'nullable';
        }

        $request->validate($rules);

        // 2. PROSES UPLOAD FOTO PROFIL (Berlaku untuk Admin & Kasir)
        if ($request->hasFile('foto_profil')) {
            $file = $request->file('foto_profil');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            
            // Hapus foto profil lama jika ada untuk menghemat kapasitas server storage
            if ($user->foto_profil && file_exists(public_path('uploads/profile/' . $user->foto_profil))) {
                @unlink(public_path('uploads/profile/' . $user->foto_profil));
            }

            $file->move(public_path('uploads/profile'), $filename);
            $user->foto_profil = $filename;
        }

        // 3. MUTASI PENGISIAN DATA GENERAL
        $user->name     = $request->name;
        $user->username = $request->username;
        $user->email    = $request->email;
        $user->no_telp  = $request->no_telp;

        // 4. KUNCI PROTEKSI: Field Alamat & Nama Toko HANYA boleh diubah jika dia adalah ADMIN
        if ($user->role === 'admin') {
            $user->nama_toko = $request->nama_toko;
            $user->alamat    = $request->alamat;
        }

        $user->save();

        return redirect()
            ->route('profile')
            ->with('success', 'Profile berhasil diperbarui');
    }
}