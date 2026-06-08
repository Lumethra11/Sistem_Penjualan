<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ManajemenBarangController;
use App\Http\Controllers\KelolaUserController;
use App\Http\Controllers\KasirController;

/*
|--------------------------------------------------------------------------
| REDIRECT ROOT
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| GUEST (BELUM LOGIN)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {

    Route::get('/login', [AuthController::class, 'loginForm'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'registerForm'])
        ->name('register');

    Route::post('/register', [AuthController::class, 'register']);
});

/*
|--------------------------------------------------------------------------
| AUTH (SUDAH LOGIN)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', function () {
        return view('dashboard.index'); // ✅ FIX DI SINI
    })->name('dashboard');

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');

    Route::get('/profile', [ProfileController::class, 'index'])
        ->name('profile');

    Route::get('/profile/edit', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::put('/profile/update', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::get('/manajemenbarang/databarang', [ManajemenBarangController::class, 'dataBarang'])
        ->name('manajemenbarang.databarang');

    Route::delete('/manajemenbarang/{id}', [ManajemenBarangController::class, 'destroy'])
        ->name('manajemenbarang.destroy');

    Route::get('/manajemenbarang/{id}/edit', [ManajemenBarangController::class, 'edit'])
        ->name('manajemenbarang.edit');
    
    Route::put('/manajemenbarang/{id}', [ManajemenBarangController::class, 'update'])
        ->name('manajemenbarang.update');

    Route::get('/manajemenbarang/formbarang', [ManajemenBarangController::class, 'formBarang'])
        ->name('manajemenbarang.formbarang');

    Route::post('/manajemenbarang/store', [ManajemenBarangController::class, 'store'])
        ->name('manajemenbarang.store');

    Route::post('/manajemenbarang/import', [ManajemenBarangController::class, 'import'])
        ->name('manajemenbarang.import');

    Route::get('/manajemenbarang/template', [ManajemenBarangController::class, 'downloadTemplate'])
        ->name('manajemenbarang.template');

    Route::get('/tambah-user', [KelolaUserController::class, 'tambahUser'])
        ->name('kelolauser.tambah');

    Route::post('/store', [KelolaUserController::class, 'store'])
        ->name('kelolauser.store');

    Route::get('/daftar-user', [KelolaUserController::class, 'daftarUser'])
        ->name('kelolauser.daftar');

    Route::put('/kelola-user/status/{id}', [KelolaUserController::class, 'toggleStatus'])
        ->name('kelolauser.status');
    
    Route::put('/kelola-user/manual-permission/{id}', [KelolaUserController::class, 'toggleManualPermission'])
        ->name('kelolauser.manual.permission');

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

});

/*
|--------------------------------------------------------------------------
| FALLBACK (JIKA URL TIDAK ADA)
|--------------------------------------------------------------------------
*/
Route::fallback(function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login'); // ✅ jangan ke welcome
});