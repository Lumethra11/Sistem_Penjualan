<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class KasirController extends Controller
{
    public function index()
    {
        // Ambil draft beserta detail dan relasi barangnya agar tidak kosong saat di-load ulang
        $drafts = Transaksi::with(['details.barang'])
            ->where('status', 'draft')
            ->latest()
            ->get();

        $barangStok = Barang::where('tipe_barang', 'stok')->get();
        $barangNonStok = Barang::where('tipe_barang', 'non_stok')->get();

        return view('kasir.transaksi', compact('drafts', 'barangStok', 'barangNonStok'));
    }

    public function riwayat()
    {
        $riwayat = Transaksi::where('status', 'selesai')
            ->latest()
            ->paginate(10);

        return view('kasir.riwayat', compact('riwayat'));
    }

    public function store(Request $request)
    {
        // FIX: Menggunakan standard validate bawaan Request untuk menghindari BadMethodCallException
        $request->validate([
            'metode_pembayaran' => 'required',
        ]);

        $status = $request->submit_type === 'selesai' ? 'selesai' : 'draft';
        $biayaJasa = $request->biaya_jasa_servis ?? 0;

        DB::beginTransaction();

        try {
            // =========================================================
            // 1. HITUNG NOMINAL PEMBAYARAN DAN KEMBALIAN SECARA AMAN
            // =========================================================
            $totalHarga = $request->total_tagihan ?? 0;
            
            // Ambil nominal bayar dari input form jika metodenya Tunai, jika non-tunai samakan dengan total harga
            $bayar = $request->metode_pembayaran === 'Tunai' ? ($request->bayar ?? 0) : $totalHarga;
            
            // Hitung nilai kembalian
            $kembalian = $bayar - $totalHarga;
            if ($kembalian < 0) { 
                $kembalian = 0; 
            }

            // =========================================================
            // 2. UPDATE ATAU BUAT TRANSAKSI UTAMA (HEAD)
            // =========================================================
            if ($request->transaksi_id) {
                $transaksi = Transaksi::findOrFail($request->transaksi_id);
                $transaksi->update([
                    'jenis_motor' => $request->jenis_motor,
                    'nomor_kendaraan' => $request->nomor_kendaraan, // Menjaga rekam data nomor kendaraan saat update
                    'total_harga' => $totalHarga,
                    'biaya_jasa_servis' => $biayaJasa,
                    'metode_pembayaran' => $request->metode_pembayaran,
                    'bayar' => $bayar,               // Menyimpan nominal uang masuk
                    'kembalian' => $kembalian,       // Menyimpan nominal uang kembalian
                    'status' => $status,
                ]);
                
                // Hapus detail lama, kita input ulang dari data form terbaru yang dikirim
                $transaksi->details()->delete();
            } else {
                $transaksi = Transaksi::create([
                    'no_invoice' => 'INV-' . strtoupper(Str::random(5)),
                    'user_id' => Auth::id(),
                    'jenis_motor' => $request->jenis_motor,
                    'nomor_kendaraan' => $request->nomor_kendaraan, // Menyimpan rekam data nomor kendaraan saat buat baru
                    'total_harga' => $totalHarga,
                    'biaya_jasa_servis' => $biayaJasa,
                    'metode_pembayaran' => $request->metode_pembayaran,
                    'bayar' => $bayar,               // Menyimpan nominal uang masuk
                    'kembalian' => $kembalian,       // Menyimpan nominal uang kembalian
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

                    // EKSEKUSI JIKA TRANSAKSI "CETAK" (SELESAI FINAL)
                    if ($status === 'selesai') {
                        
                        // JIKA BARANG MANUAL (Belum ada barang_id dari database)
                        if (empty($barangId) && !empty($namaBarang)) {
                            // Bersihkan penanda teks satuan hasil render javascript jika terbawa
                            $namaClean = preg_replace('/ \([^)]+\)$/', '', $namaBarang);

                            // Cek apakah data manual ini sebenarnya sudah terdaftar sebelumnya di master Non-Stok
                            $barangEksis = Barang::where('nama_barang', $namaClean)
                                                 ->where('tipe_barang', 'non_stok')
                                                 ->first();

                            if ($barangEksis) {
                                $barangId = $barangEksis->id;
                            } else {
                                // Fallback Otomatis Kategori & Supplier ke Master Non-Stok
                                $kategoriManual = 'Manual Kasir';
                                $supplierManual = !empty($item['supplier']) ? $item['supplier'] : 'Input Manual Kasir';

                                // Generate Kode Barang Otomatis berdasar Kategori Manual Kasir (MAN)
                                $cleanKategori = preg_replace('/[^A-Za-z]/', '', $kategoriManual);
                                $prefix = strtoupper(substr($cleanKategori, 0, 3)); // Hasil: MAN
                                $lastBarang = Barang::where('kode_barang', 'like', $prefix . '%')->orderBy('kode_barang', 'desc')->first();
                                $newNumber = $lastBarang ? intval(substr($lastBarang->kode_barang, 3)) + 1 : 1;
                                $kodeBarang = $prefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);

                                // Daftarkan langsung menjadi barang baru di data non_stok (Stok awal = 0)
                                $barangBaru = Barang::create([
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

                                // Catat pembuatan data baru ke histori_stok dengan volume 0
                                DB::table('histori_stok')->insert([
                                    'barang_id' => $barangId,
                                    'jenis_pergerakan' => 'masuk',
                                    'jumlah' => 0,
                                    'sisa_stok_saat_ini' => 0,
                                    'keterangan' => 'Barang baru (Non-Stok) dibuat otomatis dari cetak transaksi manual kasir',
                                    'created_at' => now(),
                                    'updated_at' => now()
                                ]);
                            }
                        }

                        // JIKA MENGGUNAKAN BARANG DATABASE (BAIK STOK ATAU PUN SEBELUMNYA NON-STOK)
                        if (!empty($barangId)) {
                            $barang = Barang::lockForUpdate()->find($barangId);

                            if (!$barang) {
                                throw new \Exception("Barang dengan ID {$barangId} tidak ditemukan.");
                            }

                            // Jika bertipe stok fisik, lakukan pengecekan kuantitas sisa barang
                            if ($barang->tipe_barang === 'stok') {
                                if ($barang->stok < $qty) {
                                    throw new \Exception("Gagal Cetak! Stok untuk '" . $barang->nama_barang . "' tidak mencukupi (Sisa: " . $barang->stok . ").");
                                }
                                $barang->decrement('stok', $qty);
                                $sisaStokLog = $barang->stok;
                            } else {
                                // Tipe Non-Stok dikunci di angka 0 agar tidak minus ke database master
                                $sisaStokLog = 0;
                            }

                            // Catat log pengeluaran ke histori_stok
                            DB::table('histori_stok')->insert([
                                'barang_id' => $barang->id,
                                'jenis_pergerakan' => 'keluar',
                                'jumlah' => $qty,
                                'sisa_stok_saat_ini' => $sisaStokLog,
                                'keterangan' => 'Penjualan Selesai Nota ' . $transaksi->no_invoice,
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                        }
                    }

                    // MASUKKAN ITEM KE DETAIL TRANSAKSI
                    DetailTransaksi::create([
                        'transaksi_id' => $transaksi->id,
                        'barang_id' => $barangId, // Bernilai null jika draft & manual
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

    public function nota($id)
    {
        $transaksi = Transaksi::with('details')->findOrFail($id);
        return view('kasir.nota', compact('transaksi'));
    }
}