<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class KelolaUserController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Tambah User
    |--------------------------------------------------------------------------
    |*/
    public function tambahUser()
    {
        return view('kelolauser.tambahuser');
    }

    /*
    |--------------------------------------------------------------------------
    | Simpan User
    |--------------------------------------------------------------------------
    |*/
    public function store(Request $request)
    {
        // 1. Validasi Input (Tanpa Alamat)
        $request->validate([
            'name'     => 'required|max:100',
            'username' => 'required|max:50|unique:users,username',
            'email'    => 'required|email|unique:users,email',
            'no_telp'  => 'nullable|max:20',
            'password' => 'required|min:8|confirmed',
        ], [
            // Kustomisasi pesan error bahasa Indonesia
            'name.required'     => 'Nama lengkap wajib diisi.',
            'name.max'          => 'Nama lengkap maksimal 100 karakter.',
            'username.required' => 'Username wajib diisi.',
            'username.max'      => 'Username maksimal 50 karakter.',
            'username.unique'   => 'Username ini sudah digunakan oleh akun lain.',
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'email.unique'      => 'Email ini sudah terdaftar di sistem.',
            'no_telp.max'       => 'Nomor telepon maksimal 20 karakter.',
            'password.required' => 'Password wajib diisi.',
            'password.min'      => 'Password minimal harus 8 karakter.',
            'password.confirmed'=> 'Konfirmasi password tidak cocok.',
        ]);

        // 2. Simpan Data User Kasir Baru
        User::create([
            'admin_id'                => Auth::id(),
            'role'                    => 'kasir',
            'name'                    => $request->name,
            'username'                => $request->username,
            'email'                   => $request->email,
            'no_telp'                 => $request->no_telp,
            'password'                => Hash::make($request->password),
            'is_active'               => true,
            'can_input_manual_barang' => $request->has('can_input_manual_barang'),
        ]);

        return redirect()
            ->route('kelolauser.daftar')
            ->with('success', 'Kasir berhasil ditambahkan');
    }

    /*
    |--------------------------------------------------------------------------
    | Daftar User
    |--------------------------------------------------------------------------
    |*/
    public function daftarUser()
    {
        $users = User::where('admin_id', Auth::id())
            ->where('role', 'kasir')
            ->latest()
            ->paginate(12);

        return view('kelolauser.daftaruser', compact('users'));
    }

    /*
    |--------------------------------------------------------------------------
    | Aktif / Nonaktif User
    |--------------------------------------------------------------------------
    |*/
    public function toggleStatus($id)
    {
        $user = User::where('admin_id', Auth::id())
            ->where('role', 'kasir')
            ->findOrFail($id);

        $user->is_active = !$user->is_active;
        $user->save();

        return back()->with('success', 'Status user berhasil diperbarui');
    }

    /*
    |--------------------------------------------------------------------------
    | Toggle Izin Input Barang Manual
    |--------------------------------------------------------------------------
    |*/
    public function toggleManualPermission($id)
    {
        $user = User::where('admin_id', Auth::id())
            ->where('role', 'kasir')
            ->findOrFail($id);

        $user->can_input_manual_barang = !$user->can_input_manual_barang;
        $user->save();

        return back()->with('success', 'Izin input barang manual berhasil diperbarui');
    }
}