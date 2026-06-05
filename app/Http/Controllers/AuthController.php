<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | View
    |--------------------------------------------------------------------------
    */

    public function loginForm()
    {
        return view('auth.login');
    }

    public function registerForm()
    {
        return view('auth.register');
    }

    /*
    |--------------------------------------------------------------------------
    | Register Admin
    |--------------------------------------------------------------------------
    */

    public function register(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:100',
            'username'    => 'required|string|max:50|unique:users,username',
            'email'       => 'required|email|unique:users,email',
            'nama_toko'   => 'required|string|max:100',
            'no_telp'     => 'nullable|string|max:20',
            'password'    => 'required|string|min:5|confirmed',
        ], [
            'name.required'        => 'Nama lengkap wajib diisi',
            'name.max'             => 'Nama maksimal 100 karakter',

            'username.required'    => 'Username wajib diisi',
            'username.max'         => 'Username maksimal 50 karakter',
            'username.unique'      => 'Username sudah digunakan',

            'email.required'       => 'Email wajib diisi',
            'email.email'          => 'Format email tidak valid',
            'email.unique'         => 'Email sudah terdaftar',

            'nama_toko.required'   => 'Nama toko wajib diisi',
            'nama_toko.max'        => 'Nama toko maksimal 100 karakter',

            'no_telp.max'          => 'No telepon maksimal 20 karakter',

            'password.required'    => 'Password wajib diisi',
            'password.min'         => 'Password minimal 5 karakter',
            'password.confirmed'   => 'Konfirmasi password tidak cocok',
        ]);

        User::create([
            'role'        => 'admin',
            'admin_id'    => null,

            'name'        => $request->name,
            'username'    => $request->username,
            'email'       => $request->email,

            'nama_toko'   => $request->nama_toko,
            'no_telp'     => $request->no_telp,

            'password'    => Hash::make($request->password),
        ]);

        return redirect()->route('login')
            ->with('success', 'Registrasi berhasil, silakan login.');
    }

    /*
    |--------------------------------------------------------------------------
    | Login
    |--------------------------------------------------------------------------
    */

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ], [
            'username.required' => 'Username atau email wajib diisi',
            'password.required' => 'Password wajib diisi',
        ]);

        $login = $request->username;

        $field = filter_var($login, FILTER_VALIDATE_EMAIL)
            ? 'email'
            : 'username';

        $credentials = [
            $field => $login,
            'password' => $request->password,
            'is_active' => true,
        ];

        if (Auth::attempt($credentials)) {

            $request->session()->regenerate();

            return redirect()->route('dashboard')
                ->with('success', 'Selamat datang, ' . Auth::user()->name);
        }

        return back()
            ->withInput($request->only('username'))
            ->with('error', 'Username atau password salah');
    }

    /*
    |--------------------------------------------------------------------------
    | Logout
    |--------------------------------------------------------------------------
    */

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Berhasil logout');
    }
}