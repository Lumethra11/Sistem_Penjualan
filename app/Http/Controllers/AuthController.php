<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
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
    | Register Admin (Validasi Duplikasi Ketat + FIX ALAMAT)
    |--------------------------------------------------------------------------
    */
    public function register(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:100',
            'username'    => 'required|string|max:50|unique:users,username',
            'email'       => 'required|email|unique:users,email',
            'nama_toko'   => 'required|string|max:100|unique:users,nama_toko',
            'alamat'      => 'required|string', 
            'no_telp'     => 'nullable|string|max:20|unique:users,no_telp',
            'password'    => 'required|string|min:8|confirmed',
        ], [
            'name.required'        => 'Nama lengkap wajib diisi',
            'name.max'             => 'Nama maksimal 100 karakter',

            'username.required'    => 'Username wajib diisi',
            'username.max'         => 'Username maksimal 50 karakter',
            'username.unique'      => 'Username sudah digunakan, silakan cari nama lain',

            'email.required'       => 'Email wajib diisi',
            'email.email'          => 'Format email tidak valid',
            'email.unique'         => 'Email sudah terdaftar, silakan gunakan email lain',

            'nama_toko.required'   => 'Nama toko wajib diisi',
            'nama_toko.max'        => 'Nama toko maksimal 100 karakter',
            'nama_toko.unique'     => 'Nama toko sudah terdaftar, gunakan nama unik lain',

            'alamat.required'      => 'Alamat toko wajib diisi', 

            'no_telp.max'          => 'No telepon maksimal 20 karakter',
            'no_telp.unique'       => 'No telepon sudah terdaftar pada akun lain',

            'password.required'    => 'Password wajib diisi',
            'password.min'         => 'Password minimal 8 karakter',
            'password.confirmed'   => 'Konfirmasi password tidak cocok',
        ]);

        User::create([
            'role'        => 'admin',
            'admin_id'    => null,
            'name'        => $request->name,
            'username'    => $request->username,
            'email'       => $request->email,
            'nama_toko'   => $request->nama_toko,
            'alamat'      => $request->alamat, 
            'no_telp'     => $request->no_telp,
            'password'    => Hash::make($request->password),
        ]);

        return redirect()->route('login')
            ->with('success', 'Registrasi berhasil, silakan login.');
    }

    /*
    |--------------------------------------------------------------------------
    | Login (Clean Redirect Tanpa Flash Success Pengganggu)
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
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $credentials = [
            $field => $login,
            'password' => $request->password,
        ];

        if (Auth::attempt($credentials)) {

            $request->session()->regenerate();

            $user = Auth::user();

            // FIX: Langsung arahkan tanpa membawa flash session success (.with)
            if ($user->role === 'kasir') {
                return redirect()->route('kasir.transaksi');
            }

            if ($user->role === 'admin') {
                return redirect()->route('dashboard');
            }

            // Keamanan tambahan jika role di luar ekspektasi
            Auth::logout();
            return redirect()->route('login')->with('error', 'Akses ditolak: Role akun tidak valid.');
        }

        return back()
            ->withInput($request->only('username'))
            ->with('error', 'Email / Username atau Password yang Anda masukkan salah.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')
            ->with('success', 'Berhasil logout');
    }
}