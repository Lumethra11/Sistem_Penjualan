<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ManajemenBarangController;
use App\Http\Controllers\KelolaUserController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LaporanController;

// Landing Page
Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// Route untuk pengguna yang belum login
Route::middleware('guest')->group(function () {

    Route::get('/login', [AuthController::class, 'loginForm'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'registerForm'])
        ->name('register');

    Route::post('/register', [AuthController::class, 'register']);
});

// Route untuk pengguna yang sudah login
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Detail Clustering Berfilter
    Route::get('/dashboard/clustering-detail', [DashboardController::class, 'clusteringDetail'])
        ->name('dashboard.clustering_detail');

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');

    // Profile
    Route::get('/profile', [ProfileController::class, 'index'])
        ->name('profile');

    Route::get('/profile/edit', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::put('/profile/update', [ProfileController::class, 'update'])
        ->name('profile.update');

    // Manajemen Barang
    Route::prefix('manajemenbarang')->name('manajemenbarang.')->group(function () {

        Route::get('/databarang', [ManajemenBarangController::class, 'dataBarang'])
            ->name('databarang');

        Route::get('/formbarang', [ManajemenBarangController::class, 'formBarang'])
            ->name('formbarang');

        Route::get('/rekomendasi', [ManajemenBarangController::class, 'cariRekomendasi'])
            ->name('rekomendasi');

        Route::post('/store', [ManajemenBarangController::class, 'store'])
            ->name('store');

        Route::post('/import', [ManajemenBarangController::class, 'import'])
            ->name('import');

        Route::get('/template', [ManajemenBarangController::class, 'downloadTemplate'])
            ->name('template');

        Route::get('/{id}/edit', [ManajemenBarangController::class, 'edit'])
            ->name('edit');

        Route::put('/{id}', [ManajemenBarangController::class, 'update'])
            ->name('update');

        Route::post('/{id}/pindah', [ManajemenBarangController::class, 'pindahKeStok'])
            ->name('pindah');

        Route::delete('/{id}', [ManajemenBarangController::class, 'destroy'])
            ->name('destroy');
    });

    // Kelola User
    Route::prefix('kelola-user')->name('kelolauser.')->group(function () {

        Route::get('/tambah', [KelolaUserController::class, 'tambahUser'])
            ->name('tambah');

        Route::post('/store', [KelolaUserController::class, 'store'])
            ->name('store');

        Route::get('/daftar', [KelolaUserController::class, 'daftarUser'])
            ->name('daftar');

        Route::put('/status/{id}', [KelolaUserController::class, 'toggleStatus'])
            ->name('status');

        Route::put('/manual-permission/{id}', [KelolaUserController::class, 'toggleManualPermission'])
            ->name('manual.permission');
    });

    // Kasir
    Route::prefix('kasir')->name('kasir.')->group(function () {

        Route::get('/', [KasirController::class, 'index'])
            ->name('transaksi');

        Route::get('/riwayat', [KasirController::class, 'riwayat'])
            ->name('riwayat');

        Route::post('/store', [KasirController::class, 'store'])
            ->name('store');

        Route::post('/detail/{transaksi}', [KasirController::class, 'storeDetail'])
            ->name('detail.store');

        Route::get('/nota/{id}', [KasirController::class, 'nota'])
            ->name('nota');
    });

    // Laporan
    Route::prefix('laporan')->name('laporan.')->group(function () {
        
        Route::get('/', [LaporanController::class, 'index'])
            ->name('index');
    });
});

// Jika URL tidak ditemukan
Route::fallback(function () {

    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('welcome');
});