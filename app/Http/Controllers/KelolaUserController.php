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

            'can_input_manual_barang'
                => $request->has('can_input_manual_barang'),
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
    | Aktif / Nonaktif User
    |--------------------------------------------------------------------------
    */

    public function toggleStatus($id)
    {
        $user = User::where('admin_id', Auth::id())
            ->where('role', 'kasir')
            ->findOrFail($id);

        $user->is_active = !$user->is_active;

        $user->save();

        return back()->with(
            'success',
            'Status user berhasil diperbarui'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Toggle Izin Input Barang Manual
    |--------------------------------------------------------------------------
    */

    public function toggleManualPermission($id)
    {
        $user = User::where('admin_id', Auth::id())
            ->where('role', 'kasir')
            ->findOrFail($id);

        $user->can_input_manual_barang =
            !$user->can_input_manual_barang;

        $user->save();

        return back()->with(
            'success',
            'Izin input barang manual berhasil diperbarui'
        );
    }
}