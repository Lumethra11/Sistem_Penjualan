<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Barang;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TestingKMeansSeeder extends Seeder
{
    public function run(): void
    {
        // 1. CLEAR DATA LAMA UNTUK TESTING FRESH SEEDING
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DetailTransaksi::truncate();
        Transaksi::truncate();
        DB::table('histori_stok')->truncate();
        DB::table('notifikasi')->truncate(); 
        DB::table('riwayat_clustering')->truncate(); // Bersihkan juga riwayat clustering lama agar tidak conflict
        Barang::truncate();
        User::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 2. SEEDER USER: ADMIN & KASIR (Ekosistem terisolasi untuk Bengkel AA Motor)
        $admin = User::create([
            'admin_id' => null,
            'role' => 'admin',
            'name' => 'Owner Bengkel AA Motor',
            'username' => 'admin',
            'email' => 'iqbaldeban11@gmail.com',
            'nama_toko' => 'Bengkel AA Motor',
            'no_telp' => '081234567890',
            'alamat' => "Jl. Raya Jember No. 45\nBanyuwangi, Jawa Timur",
            'is_active' => true,
            'can_input_manual_barang' => true,
            'password' => Hash::make('password123'),
        ]);

        $kasir = User::create([
            'admin_id' => $admin->id,
            'role' => 'kasir',
            'name' => 'Fulan bin Fulanah',
            'username' => 'kasir',
            'email' => 'kasir@aamotor.com',
            'nama_toko' => null,
            'no_telp' => '089876543210',
            'alamat' => null,
            'is_active' => true,
            'can_input_manual_barang' => false,
            'password' => Hash::make('password123'),
        ]);

        // 3. MASTER DATA 20 BARANG (Scoped ke user_id milik Admin/Bengkel)
        $masterBarang = [
            ['kode_barang' => 'B-001', 'nama_barang' => 'Kampas KPH Ⓐ', 'stok' => 15, 'satuan' => 'Pcs', 'harga_beli' => 35000, 'harga_jual' => 45000, 'kategori' => 'Pengereman', 'supplier' => 'Putra Otomotif', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'B-002', 'nama_barang' => 'Kampas Mio Aspira', 'stok' => 15, 'satuan' => 'Pcs', 'harga_beli' => 31500, 'harga_jual' => 40000, 'kategori' => 'Pengereman', 'supplier' => 'Putra Otomotif', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'B-003', 'nama_barang' => 'Dispad KVB/Vario', 'stok' => 15, 'satuan' => 'Pcs', 'harga_beli' => 19000, 'harga_jual' => 28000, 'kategori' => 'Pengereman', 'supplier' => 'Putra Otomotif', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'B-004', 'nama_barang' => 'Kampas Kopling Grand', 'stok' => 10, 'satuan' => 'Pcs', 'harga_beli' => 7000, 'harga_jual' => 8500, 'kategori' => 'Engine', 'supplier' => 'Putra Otomotif', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'B-005', 'nama_barang' => 'Pack Kop Grand', 'stok' => 20, 'satuan' => 'Pcs', 'harga_beli' => 7750, 'harga_jual' => 12000, 'kategori' => 'Packing', 'supplier' => 'Putra Otomotif', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'B-006', 'nama_barang' => 'Kabel Gas', 'stok' => 40, 'satuan' => 'Pcs', 'harga_beli' => 4500, 'harga_jual' => 10000, 'kategori' => 'Engine', 'supplier' => 'Putra Otomotif', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'B-007', 'nama_barang' => 'Baut Flange 6x20', 'stok' => 100, 'satuan' => 'Pcs', 'harga_beli' => 750, 'harga_jual' => 1500, 'kategori' => 'Baut', 'supplier' => 'Putra Otomotif', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'B-008', 'nama_barang' => 'Baut Flange 8x20', 'stok' => 100, 'satuan' => 'Pcs', 'harga_beli' => 1250, 'harga_jual' => 2500, 'kategori' => 'Baut', 'supplier' => 'Putra Otomotif', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'B-009', 'nama_barang' => 'Skrup Filter Beat', 'stok' => 43, 'satuan' => 'Pcs', 'harga_beli' => 400, 'harga_jual' => 2000, 'kategori' => 'Baut', 'supplier' => 'Putra Otomotif', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'B-010', 'nama_barang' => 'Kancingan Rantai 428H', 'stok' => 49, 'satuan' => 'Pcs', 'harga_beli' => 1400, 'harga_jual' => 2000, 'kategori' => 'Engine', 'supplier' => 'Putra Otomotif', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'B-011', 'nama_barang' => 'Baut Tap Yamaha', 'stok' => 46, 'satuan' => 'Pcs', 'harga_beli' => 6750, 'harga_jual' => 10000, 'kategori' => 'Baut', 'supplier' => 'Putra Otomotif', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'B-012', 'nama_barang' => 'Mur As Roda (M14)', 'stok' => 40, 'satuan' => 'Pcs', 'harga_beli' => 3500, 'harga_jual' => 8500, 'kategori' => 'Baut', 'supplier' => 'Putra Otomotif', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'B-013', 'nama_barang' => 'Per Standar Samping Honda', 'stok' => 30, 'satuan' => 'Pcs', 'harga_beli' => 3000, 'harga_jual' => 7000, 'kategori' => 'Baut', 'supplier' => 'Putra Otomotif', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'B-014', 'nama_barang' => 'Gantungan Barang', 'stok' => 30, 'satuan' => 'Pcs', 'harga_beli' => 2000, 'harga_jual' => 5000, 'kategori' => 'Baut', 'supplier' => 'Putra Otomotif', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'B-015', 'nama_barang' => 'Mur Pully Mio', 'stok' => 40, 'satuan' => 'Pcs', 'harga_beli' => 4000, 'harga_jual' => 8000, 'kategori' => 'Baut', 'supplier' => 'Putra Otomotif', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'B-016', 'nama_barang' => 'Baut JP 6x35', 'stok' => 100, 'satuan' => 'Pcs', 'harga_beli' => 500, 'harga_jual' => 2000, 'kategori' => 'Baut', 'supplier' => 'Putra Otomotif', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'B-017', 'nama_barang' => 'Baut Kontak', 'stok' => 97, 'satuan' => 'Pcs', 'harga_beli' => 120, 'harga_jual' => 1000, 'kategori' => 'Baut', 'supplier' => 'Putra Otomotif', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'B-018', 'nama_barang' => 'Sekering Kotak', 'stok' => 30, 'satuan' => 'Pcs', 'harga_beli' => 500, 'harga_jual' => 2000, 'kategori' => 'Kelistrikan', 'supplier' => 'Putra Otomotif', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'B-019', 'nama_barang' => 'Baut Tutup / Hood Klep', 'stok' => 98, 'satuan' => 'Pcs', 'harga_beli' => 6000, 'harga_jual' => 8000, 'kategori' => 'Baut', 'supplier' => 'Putra Otomotif', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'B-020', 'nama_barang' => 'Baut Per Aki', 'stok' => 199, 'satuan' => 'Pcs', 'harga_beli' => 500, 'harga_jual' => 1500, 'kategori' => 'Baut', 'supplier' => 'Putra Otomotif', 'tipe_barang' => 'stok'],
        ];

        DB::beginTransaction();
        try {
            $barangInstances = [];
            foreach ($masterBarang as $mb) {
                // FIX 1: Menyuntikkan user_id Admin bengkel agar barang terdaftar sah secara multi-tenant
                $mb['user_id'] = $admin->id;
                $barangInstances[$mb['kode_barang']] = Barang::create($mb);
            }

            $startPeriod = Carbon::now()->subDays(7)->startOfDay();
            $endPeriod = Carbon::now()->subDay()->endOfDay();

            $this->command->info("Menyuntikkan data transaksi murni periode: " . $startPeriod->toDateString() . " s/d " . $endPeriod->toDateString());

            // Naikkan jumlah transaksi per hari agar sebaran data lebih luas (bengkel ramai)
            for ($date = clone $startPeriod; $date->lte($endPeriod); $date->addDay()) {
                $jumlahTransaksiHariIni = rand(8, 15); 

                for ($inv = 1; $inv <= $jumlahTransaksiHariIni; $inv++) {
                    $waktuSpesifik = clone $date;
                    $waktuSpesifik->setTime(rand(8, 16), rand(10, 59));

                    $noInvoice = 'INV-' . $waktuSpesifik->format('ymd') . '-' . str_pad($inv, 2, '0', STR_PAD_LEFT);
                    
                    $kodeWilayah = ['P', 'DK', 'L', 'N', 'B'][rand(0, 4)];
                    $platNomorSimulasi = $kodeWilayah . ' ' . rand(1000, 9999) . ' ' . chr(rand(65, 90)) . chr(rand(65, 90));

                    $transaksi = Transaksi::create([
                        'no_invoice'        => $noInvoice,
                        'user_id'           => $kasir->id,
                        'jenis_motor'       => ['Beat FI', 'Vario 125', 'Scoopy ESP', 'Mio M3', 'Supra X'][rand(0, 4)],
                        'nomor_kendaraan'   => $platNomorSimulasi,
                        'biaya_jasa_servis' => [0, 25000, 35000][rand(0, 2)],
                        'total_harga'       => 0,
                        'metode_pembayaran' => ['Tunai', 'Transfer', 'QRIS'][rand(0, 2)],
                        'status'            => 'selesai',
                        'bayar'             => 0,
                        'kembalian'         => 0,
                        'created_at'        => $waktuSpesifik,
                        'updated_at'        => $waktuSpesifik,
                    ]);

                    $subtotalInvoice = 0;
                    $itemsNotaIni = [];

                    // DEFINISI KELOMPOK TARGET AGAR K-MEANS DAPAT POLA STRUKTUR JELAS
                    // Kelompok Laris: Baut umum & Kabel Gas
                    $fastItems = ['B-006', 'B-007', 'B-008', 'B-009', 'B-016', 'B-017', 'B-020'];
                    // Kelompok Sedang: Per, Mur, Gantungan, Sekering
                    $mediumItems = ['B-005', 'B-010', 'B-011', 'B-012', 'B-013', 'B-014', 'B-015', 'B-018', 'B-019'];
                    // Kelompok Lambat: Kampas Rem & Komponen Pengereman Vital
                    $slowItems = ['B-001', 'B-002', 'B-003', 'B-004'];

                    // Logika pengisian nota belanjaan konsumen secara acak terstruktur
                    if (rand(1, 100) <= 80) { // 80% kemungkinan beli barang fast-moving
                        $itemsNotaIni[] = $fastItems[array_rand($fastItems)];
                        if (rand(1, 100) <= 30) { 
                            $itemsNotaIni[] = $fastItems[array_rand($fastItems)];
                        }
                    }

                    if (rand(1, 100) <= 45) { // 45% kemungkinan beli barang medium-moving
                        $itemsNotaIni[] = $mediumItems[array_rand($mediumItems)];
                    }

                    if (rand(1, 100) <= 12) { // Hanya 12% kemungkinan ganti kampas rem (slow-moving)
                        $itemsNotaIni[] = $slowItems[array_rand($slowItems)];
                    }

                    $itemsNotaIni = array_unique($itemsNotaIni);

                    foreach ($itemsNotaIni as $kodeB) {
                        $barang = $barangInstances[$kodeB];
                        
                        if (in_array($kodeB, $fastItems)) {
                            $qtyJual = rand(2, 4); 
                        } else if (in_array($kodeB, $mediumItems)) {
                            $qtyJual = rand(1, 2);
                        } else {
                            $qtyJual = 1; 
                        }

                        // Proteksi mutlak agar stok riil tidak minus
                        if ($barang->stok < $qtyJual) {
                            continue;
                        }

                        $barang->decrement('stok', $qtyJual);
                        $subtotalItem = $barang->harga_jual * $qtyJual;

                        DetailTransaksi::create([
                            'transaksi_id'      => $transaksi->id,
                            'barang_id'         => $barang->id,
                            'nama_barang_manual'=> null,
                            'harga_modal'       => $barang->harga_beli,
                            'harga_jual_satuan' => $barang->harga_jual,
                            'jumlah'            => $qtyJual,
                            'subtotal'          => $subtotalItem,
                            'created_at'        => $waktuSpesifik,
                            'updated_at'        => $waktuSpesifik,
                        ]);

                        // FIX 2: Menyertakan field user_id (ID Admin Bengkel) pada pembuatan log histori_stok
                        DB::table('histori_stok')->insert([
                            'user_id'           => $admin->id,
                            'barang_id'         => $barang->id,
                            'jenis_pergerakan'  => 'keluar',
                            'jumlah'            => $qtyJual,
                            'sisa_stok_saat_ini'=> $barang->stok,
                            'keterangan'        => 'Penjualan Otomatis ' . $noInvoice,
                            'created_at'        => $waktuSpesifik,
                            'updated_at'        => $waktuSpesifik,
                        ]);

                        $subtotalInvoice += $subtotalItem;
                    }

                    $totalAkhirInvoice = $subtotalInvoice + $transaksi->biaya_jasa_servis;
                    $bayar = $totalAkhirInvoice;
                    $kembalian = 0;

                    if ($transaksi->metode_pembayaran === 'Tunai' && $totalAkhirInvoice > 0) {
                        if ($totalAkhirInvoice % 10000 !== 0) $bayar = (ceil($totalAkhirInvoice / 10000) * 10000);
                        $kembalian = $bayar - $totalAkhirInvoice;
                    }

                    $transaksi->update([
                        'total_harga' => $totalAkhirInvoice,
                        'bayar'       => $bayar,
                        'kembalian'   => $kembalian
                    ]);
                }
            }

            DB::commit();
            $this->command->info("==========================================================================");
            $this->command->info(" SEEDER BERHASIL UPDATE: Distribusi data penjualan barang kini bervariasi.");
            $this->command->info(" Gap Cluster Laris, Sedang, Kurang Laris akan terlihat kontras di K-Means.");
            $this->command->info("==========================================================================");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("Gagal Eksekusi Seeder: " . $e->getMessage());
        }
    }
}