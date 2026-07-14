<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class KasirController extends Controller
{
    /**
     * HALAMAN UTAMA TRANSAKSI KASIR
     */
    public function index()
    {
        $user = Auth::user();
        // JANGKAR MULTI-TENANT: Mengunci ruang data pada ID Admin Pemilik Bengkel
        $adminId = ($user->role === 'admin') ? $user->id : $user->admin_id;

        // 1. Ambil draft transaksi yang dibuat oleh kru internal bengkel ini saja
        $drafts = Transaksi::with(['details.barang'])
            ->where('status', 'draft')
            ->whereHas('user', function($q) use ($adminId) {
                $q->where('id', $adminId)->orWhere('admin_id', $adminId);
            })
            ->latest()
            ->get();

        // 2. Filter inventaris barang agar kasir hanya melihat stok milik bengkelnya sendiri
        $barangStok = Barang::where('user_id', $adminId)->where('tipe_barang', 'stok')->get();
        $barangNonStok = Barang::where('user_id', $adminId)->where('tipe_barang', 'non_stok')->get();

        return view('kasir.transaksi', compact('drafts', 'barangStok', 'barangNonStok'));
    }

    /**
     * HALAMAN RIWAYAT TRANSAKSI FINAL
     */
    public function riwayat()
    {
        $user = Auth::user();
        $adminId = ($user->role === 'admin') ? $user->id : $user->admin_id;

        // Ambil riwayat selesai yang dibuat oleh internal ekosistem bengkel ini
        $riwayat = Transaksi::where('status', 'selesai')
            ->whereHas('user', function($q) use ($adminId) {
                $q->where('id', $adminId)->orWhere('admin_id', $adminId);
            })
            ->latest()
            ->paginate(10);

        return view('kasir.riwayat', compact('riwayat'));
    }

    /**
     * PROSES SIMPAN TRANSAKSI (DRAFT ATAU SELESAI FINAL)
     */
    public function store(Request $request)
    {
        $request->validate([
            'metode_pembayaran' => 'required',
        ]);

        $user = Auth::user();
        $adminId = ($user->role === 'admin') ? $user->id : $user->admin_id;

        $status = $request->submit_type === 'selesai' ? 'selesai' : 'draft';
        $biayaJasa = $request->biaya_jasa_servis ?? 0;

        DB::beginTransaction();

        try {
            // =========================================================
            // 1. HITUNG NOMINAL PEMBAYARAN DAN KEMBALIAN SECARA AMAN
            // =========================================================
            $totalHarga = $request->total_tagihan ?? 0;
            $bayar = $request->metode_pembayaran === 'Tunai' ? ($request->bayar ?? 0) : $totalHarga;
            
            $kembalian = $bayar - $totalHarga;
            if ($kembalian < 0) { 
                $kembalian = 0; 
            }

            // =========================================================
            // 2. UPDATE ATAU BUAT TRANSAKSI UTAMA (HEAD)
            // =========================================================
            if ($request->transaksi_id) {
                // Cari transaksi, pastikan transaksi tersebut milik kru bengkel ini (mencegah SQL injection silang)
                $transaksi = Transaksi::whereHas('user', function($q) use ($adminId) {
                        $q->where('id', $adminId)->orWhere('admin_id', $adminId);
                    })->findOrFail($request->transaksi_id);

                $transaksi->update([
                    'jenis_motor' => $request->jenis_motor,
                    'nomor_kendaraan' => $request->nomor_kendaraan,
                    'total_harga' => $totalHarga,
                    'biaya_jasa_servis' => $biayaJasa,
                    'metode_pembayaran' => $request->metode_pembayaran,
                    'bayar' => $bayar,
                    'kembalian' => $kembalian,
                    'status' => $status,
                ]);
                
                $transaksi->details()->delete();
            } else {
                $transaksi = Transaksi::create([
                    'no_invoice' => 'INV-' . strtoupper(Str::random(5)),
                    'user_id' => $user->id, // Pencatat tetap ID akun asli (Kasir/Admin yang bertugas)
                    'jenis_motor' => $request->jenis_motor,
                    'nomor_kendaraan' => $request->nomor_kendaraan,
                    'total_harga' => $totalHarga,
                    'biaya_jasa_servis' => $biayaJasa,
                    'metode_pembayaran' => $request->metode_pembayaran,
                    'bayar' => $bayar,
                    'kembalian' => $kembalian,
                    'status' => $status,
                ]);
            }

            // =========================================================
            // 3. PROSES DETAIL ITEMS & LOGIKA MUTASI STOK
            // =========================================================
            if ($request->has('items')) {
                foreach ($request->items as $item) {
                    $barangId = !empty($item['barang_id']) ? $item['barang_id'] : null;
                    $namaBarang = $item['nama_barang'] ?? null;
                    $hargaBeli = $item['harga_beli'] ?? 0;
                    $hargaJual = $item['harga'] ?? 0;
                    $qty = intval($item['qty']);

                    if ($status === 'selesai') {
                        
                        // JIKA BARANG MANUAL (Belum ada di DB, Kasir ketik teks bebas)
                        if (empty($barangId) && !empty($namaBarang)) {
                            $namaClean = preg_replace('/ \([^)]+\)$/', '', $namaBarang);

                            // Cek apakah produk manual ini sudah pernah didaftarkan sebelumnya DI BENGKEL INI
                            $barangEksis = Barang::where('user_id', $adminId)
                                ->where('nama_barang', $namaClean)
                                ->where('tipe_barang', 'non_stok')
                                ->first();

                            if ($barangEksis) {
                                $barangId = $barangEksis->id;
                            } else {
                                $kategoriManual = 'Manual Kasir';
                                $supplierManual = !empty($item['supplier']) ? $item['supplier'] : 'Input Manual Kasir';

                                // Generate urutan kode barang non-stok terisolasi khusus bengkel ini
                                $cleanKategori = preg_replace('/[^A-Za-z]/', '', $kategoriManual);
                                $prefix = strtoupper(substr($cleanKategori, 0, 3)); // MAN
                                
                                $lastBarang = Barang::where('user_id', $adminId)
                                    ->where('kode_barang', 'like', $prefix . '%')
                                    ->orderBy('kode_barang', 'desc')
                                    ->first();
                                    
                                $newNumber = $lastBarang ? intval(substr($lastBarang->kode_barang, 3)) + 1 : 1;
                                $kodeBarang = $prefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);

                                // Kunci barang baru non-stok ini dengan user_id milik ADMIN pemilik bengkel
                                $barangBaru = Barang::create([
                                    'user_id'     => $adminId, 
                                    'kode_barang' => $kodeBarang,
                                    'nama_barang' => $namaClean,
                                    'tipe_barang' => 'non_stok',
                                    'stok'        => 0,
                                    'satuan'      => 'Pcs',
                                    'harga_beli'  => $hargaBeli,
                                    'harga_jual'  => $hargaJual,
                                    'kategori'    => $kategoriManual,
                                    'supplier'    => $supplierManual,
                                ]);

                                $barangId = $barangBaru->id;

                                // FIX 1: Menambahkan user_id (akun kasir/admin yang sedang bertugas) saat log barang manual baru
                                DB::table('histori_stok')->insert([
                                    'user_id'            => auth()->id(),
                                    'barang_id'          => $barangId,
                                    'jenis_pergerakan'   => 'masuk',
                                    'jumlah'             => 0,
                                    'sisa_stok_saat_ini' => 0,
                                    'keterangan'         => 'Barang baru (Non-Stok) dibuat otomatis dari cetak transaksi manual kasir',
                                    'created_at'         => now(),
                                    'updated_at'         => now()
                                ]);
                            }
                        }

                        // JIKA MENGGUNAKAN BARANG DATABASE BENGKEL
                        if (!empty($barangId)) {
                            // Amankan item barang: Harus terdaftar di bawah kepemilikan Bengkel ini
                            $barang = Barang::where('user_id', $adminId)->lockForUpdate()->find($barangId);

                            if (!$barang) {
                                throw new \Exception("Barang tidak ditemukan atau bukan milik otorisasi bengkel Anda.");
                            }

                            if ($barang->tipe_barang === 'stok') {
                                if ($barang->stok < $qty) {
                                    throw new \Exception("Gagal Cetak! Stok untuk '" . $barang->nama_barang . "' tidak mencukupi (Sisa: " . $barang->stok . ").");
                                }
                                $barang->decrement('stok', $qty);
                                $sisaStokLog = $barang->stok;
                            } else {
                                $sisaStokLog = 0;
                            }

                            // FIX 2: Menambahkan user_id (akun kasir/admin yang sedang bertugas) pada mutasi stok penjualan
                            DB::table('histori_stok')->insert([
                                'user_id'            => auth()->id(),
                                'barang_id'          => $barang->id,
                                'jenis_pergerakan'   => 'keluar',
                                'jumlah'             => $qty,
                                'sisa_stok_saat_ini' => $sisaStokLog,
                                'keterangan'         => 'Penjualan Selesai Nota ' . $transaksi->no_invoice,
                                'created_at'         => now(),
                                'updated_at'         => now()
                            ]);
                        }
                    }

                    // MASUKKAN ITEM KE DETAIL TRANSAKSI
                    DetailTransaksi::create([
                        'transaksi_id' => $transaksi->id,
                        'barang_id' => $barangId,
                        'nama_barang_manual' => empty($barangId) ? $namaBarang : null,
                        'harga_modal' => $hargaBeli,
                        'harga_jual_satuan' => $hargaJual,
                        'jumlah' => $qty,
                        'subtotal' => $hargaJual * $qty,
                    ]);
                }
            }

            DB::commit();

            if ($status === 'selesai') {
                return redirect()->route('kasir.riwayat')
                    ->with('cetak_nota', $transaksi->id)
                    ->with('success', 'Transaksi Selesai & Berhasil Dicetak');
            }

            return redirect()->route('kasir.transaksi')->with('success', 'Draft transaksi berhasil disimpan/diperbarui');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('kasir.transaksi')->with('error', $e->getMessage());
        }
    }

    /**
     * TAMPILAN NOTA STRUK KASIR
     */
    public function nota($id)
    {
        $user = Auth::user();
        $adminId = ($user->role === 'admin') ? $user->id : $user->admin_id;

        // Validasi Otoritas Nota: Ambil nota jika dibuat oleh kru bengkel internal ini saja
        $transaksi = Transaksi::with('details')
            ->whereHas('user', function($q) use ($adminId) {
                $q->where('id', $adminId)->orWhere('admin_id', $adminId);
            })
            ->findOrFail($id);

        return view('kasir.nota', compact('transaksi'));
    }
}