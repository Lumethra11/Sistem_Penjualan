<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ForgotPasswordController extends Controller
{
    /**
     * Menampilkan halaman form input email lupa password
     */
    public function showLinkRequestForm()
    {
        // Mengarah ke resources/views/auth/lupapassword.blade.php
        return view('auth.lupapassword'); 
    }

    /**
     * Memproses pengiriman email link reset password
     */
    public function sendResetLinkEmail(Request $request)
    {
        // 1. Validasi input email wajib terdaftar di tabel users
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.exists' => 'Email tersebut tidak terdaftar di sistem kami.'
        ]);

        $token = Str::random(64);

        // 2. Simpan atau perbarui token ke database password_reset_tokens
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => $token,
                'created_at' => Carbon::now() // Menyimpan waktu pembuatan token
            ]
        );

        // 3. Kirim Email Menggunakan View Blade Kustom (auth.email)
        try {
            $url = route('password.reset', ['token' => $token, 'email' => $request->email]);

            Mail::send('auth.email', ['url' => $url], function ($message) use ($request) {
                // Mengatur nama pengirim menjadi Sistem Penjualan secara bersih dari nama Laravel
                $message->from(config('mail.from.address'), 'Sistem Penjualan');
                $message->to($request->email);
                $message->subject('[Sistem Penjualan] Permintaan Reset Password Akun Anda');
            });

        } catch (\Exception $e) {
            // Jika SMTP/Google error, tangkap dan munculkan pesan error aslinya di halaman alert
            return back()->withInput()->with('error', 'Gagal mengirim email: ' . $e->getMessage());
        }

        return back()->with('success', 'Link reset password telah dikirim ke kotak masuk atau folder spam email Anda!');
    }

    /**
     * Menampilkan halaman form input password baru (diakses dari Link Email)
     */
    public function showResetForm($token, Request $request)
    {
        // Cek apakah token ada dan belum kedaluwarsa sebelum menampilkan form
        $checkToken = DB::table('password_reset_tokens')
            ->where([
                'email' => $request->email,
                'token' => $token
            ])->first();

        // Validasi tambahan: Jika link di-klik saat sudah lewat 60 menit
        if ($checkToken) {
            $waktuPembuatan = Carbon::parse($checkToken->created_at);
            if ($waktuPembuatan->addMinutes(60)->isPast()) {
                return redirect()->route('password.request')->with('error', 'Link reset password sudah kedaluwarsa (Maksimal 60 menit). Silakan minta link baru.');
            }
        }

        // Mengarah ke resources/views/auth/resetpassword.blade.php
        return view('auth.resetpassword', ['token' => $token, 'email' => $request->email]);
    }

    /**
     * Memproses update password baru ke database
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // 1. Cek validitas token di database
        $checkToken = DB::table('password_reset_tokens')
            ->where([
                'email' => $request->email,
                'token' => $request->token
            ])->first();

        if (!$checkToken) {
            return back()->withInput()->with('error', 'Token reset password tidak valid.');
        }

        // 2. LOGIKA FIX: Cek apakah token sudah kedaluwarsa (Lebih dari 60 menit)
        $waktuPembuatan = Carbon::parse($checkToken->created_at);
        if ($waktuPembuatan->addMinutes(60)->isPast()) {
            // Hapus token yang sudah basi agar bersih
            DB::table('password_reset_tokens')->where(['email' => $request->email])->delete();
            
            return redirect()->route('password.request')->with('error', 'Link reset password Anda telah kedaluwarsa karena sudah melewati batas 60 menit. Silakan ajukan ulang.');
        }

        // 3. Update password user baru (Menggunakan bcrypt)
        DB::table('users')->where('email', $request->email)->update([
            'password' => bcrypt($request->password)
        ]);

        // 4. Hapus token lama agar tidak bisa digunakan kembali
        DB::table('password_reset_tokens')->where(['email' => $request->email])->delete();

        return redirect()->route('login')->with('success', 'Password Anda berhasil diperbarui! Silakan login kembali.');
    }
}