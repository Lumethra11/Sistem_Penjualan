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
        $request->validate([
            'name' => 'required|max:255',
            'username' => 'required|max:255|unique:users,username,' . Auth::id(),
            'email' => 'required|email|unique:users,email,' . Auth::id(),
            'nama_toko' => 'nullable|max:255',
            'no_telp' => 'nullable|max:255',
            'alamat' => 'nullable',
            'foto_profil' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $user = Auth::user();

        // FOTO
        if ($request->hasFile('foto_profil')) {

            $file = $request->file('foto_profil');

            $filename = time() . '.' . $file->getClientOriginalExtension();

            $file->move(public_path('uploads/profile'), $filename);

            $user->foto_profil = $filename;
        }

        $user->name = $request->name;
        $user->username = $request->username;
        $user->email = $request->email;
        $user->nama_toko = $request->nama_toko;
        $user->no_telp = $request->no_telp;
        $user->alamat = $request->alamat;

        $user->save();

        return redirect()
            ->route('profile')
            ->with('success', 'Profile berhasil diperbarui');
    }
}