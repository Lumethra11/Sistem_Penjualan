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
    */

    public function tambahUser()
    {
        return view('kelolauser.tambahuser');
    }

    /*
    |--------------------------------------------------------------------------
    | Simpan User
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:100',
            'username' => 'required|max:50|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'no_telp' => 'nullable|max:20',
            'alamat' => 'nullable',
            'password' => 'required|min:5|confirmed',
        ]);

        User::create([
            'admin_id' => Auth::id(),
            'role' => 'kasir',

            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,

            'no_telp' => $request->no_telp,
            'alamat' => $request->alamat,

            'password' => Hash::make($request->password),

            'is_active' => true,
        ]);

        return redirect()
            ->route('kelolauser.daftar')
            ->with('success', 'User berhasil ditambahkan');
    }

    /*
    |--------------------------------------------------------------------------
    | Daftar User
    |--------------------------------------------------------------------------
    */

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
    | Toggle Status
    |--------------------------------------------------------------------------
    */

    public function toggleStatus($id)
    {
        $user = User::where('admin_id', Auth::id())
            ->findOrFail($id);

        $user->is_active = !$user->is_active;

        $user->save();

        return back()->with('success', 'Status user berhasil diperbarui');
    }
}