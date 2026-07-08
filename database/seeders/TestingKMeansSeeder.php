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
        Barang::truncate();
        User::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 2. SEEDER USER: ADMIN & KASIR
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

        // 3. MASTER DATA 20 BARANG (Stok awal dibuat aman)
        $masterBarang = [
            ['kode_barang' => 'B-001', 'nama_barang' => 'Aki GS YTZ5S', 'stok' => 200, 'satuan' => 'Pcs', 'harga_beli' => 155000, 'harga_jual' => 185000, 'kategori' => 'Kelistrikan', 'supplier' => 'Putra Otomotif', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'B-002', 'nama_barang' => 'Aki GS YTZ6V', 'stok' => 200, 'satuan' => 'Pcs', 'harga_beli' => 210000, 'harga_jual' => 245000, 'kategori' => 'Kelistrikan', 'supplier' => 'Putra Otomotif', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'B-003', 'nama_barang' => 'Ban Luar IRC 80/90-14 NF59', 'stok' => 200, 'satuan' => 'Pcs', 'harga_beli' => 145000, 'harga_jual' => 175000, 'kategori' => 'Ban', 'supplier' => 'Putra Otomotif', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'B-004', 'nama_barang' => 'Ban Luar IRC 90/90-14 NR73', 'stok' => 200, 'satuan' => 'Pcs', 'harga_beli' => 175000, 'harga_jual' => 210000, 'kategori' => 'Ban', 'supplier' => 'Putra Otomotif', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'B-005', 'nama_barang' => 'Ban Dalam IRC 2.50/2.75-14', 'stok' => 300, 'satuan' => 'Pcs', 'harga_beli' => 28000, 'harga_jual' => 38000, 'kategori' => 'Ban', 'supplier' => 'Putra Otomotif', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'B-006', 'nama_barang' => 'Busi Denso U27EPR-9', 'stok' => 500, 'satuan' => 'Pcs', 'harga_beli' => 12500, 'harga_jual' => 20000, 'kategori' => 'Pengapian', 'supplier' => 'Denso Indonesia', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'B-007', 'nama_barang' => 'Busi NGK CPR9EA-9', 'stok' => 500, 'satuan' => 'Pcs', 'harga_beli' => 13500, 'harga_jual' => 22000, 'kategori' => 'Pengapian', 'supplier' => 'Denso Indonesia', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'B-008', 'nama_barang' => 'Baut Blok Mesin 12mm', 'stok' => 500, 'satuan' => 'Pcs', 'harga_beli' => 1000, 'harga_jual' => 2500, 'kategori' => 'Mur & Baut', 'supplier' => 'Putra Otomotif', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'B-009', 'nama_barang' => 'Baut Knalpot Honda Ring 14', 'stok' => 500, 'satuan' => 'Pcs', 'harga_beli' => 1500, 'harga_jual' => 3500, 'kategori' => 'Mur & Baut', 'supplier' => 'Putra Otomotif', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'B-010', 'nama_barang' => 'Filter Udara Beat FI', 'stok' => 250, 'satuan' => 'Pcs', 'harga_beli' => 30000, 'harga_jual' => 42000, 'kategori' => 'Filter', 'supplier' => 'PT Astra Honda', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'B-011', 'nama_barang' => 'Filter Udara Vario 125', 'stok' => 250, 'satuan' => 'Pcs', 'harga_beli' => 32000, 'harga_jual' => 45000, 'kategori' => 'Filter', 'supplier' => 'PT Astra Honda', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'B-012', 'nama_barang' => 'Filter Udara Mio M3', 'stok' => 200, 'satuan' => 'Pcs', 'harga_beli' => 28000, 'harga_jual' => 38000, 'kategori' => 'Filter', 'supplier' => 'Yamaha Motor Parts', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'B-013', 'nama_barang' => 'Gear Set Supra X 125', 'stok' => 150, 'satuan' => 'Set', 'harga_beli' => 140000, 'harga_jual' => 175000, 'kategori' => 'Suku Cadang', 'supplier' => 'PT Astra Honda', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'B-014', 'nama_barang' => 'Gear Set Jupiter Z', 'stok' => 150, 'satuan' => 'Set', 'harga_beli' => 135000, 'harga_jual' => 165000, 'kategori' => 'Suku Cadang', 'supplier' => 'Yamaha Motor Parts', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'B-015', 'nama_barang' => 'Kampas Rem Depan Beat FI', 'stok' => 300, 'satuan' => 'Pcs', 'harga_beli' => 22000, 'harga_jual' => 32000, 'kategori' => 'Pengereman', 'supplier' => 'PT Astra Honda', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'B-016', 'nama_barang' => 'Kampas Rem Belakang Vario', 'stok' => 300, 'satuan' => 'Pcs', 'harga_beli' => 26000, 'harga_jual' => 38000, 'kategori' => 'Pengereman', 'supplier' => 'PT Astra Honda', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'B-017', 'nama_barang' => 'Kampas Rem Depan Mio', 'stok' => 300, 'satuan' => 'Pcs', 'harga_beli' => 20000, 'harga_jual' => 30000, 'kategori' => 'Pengereman', 'supplier' => 'Yamaha Motor Parts', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'B-018', 'nama_barang' => 'Kampas Rem Belakang Mio', 'stok' => 300, 'satuan' => 'Pcs', 'harga_beli' => 24000, 'harga_jual' => 35000, 'kategori' => 'Pengereman', 'supplier' => 'Yamaha Motor Parts', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'B-019', 'nama_barang' => 'Kampas Kopling Ganda Beat', 'stok' => 150, 'satuan' => 'Pcs', 'harga_beli' => 95000, 'harga_jual' => 125000, 'kategori' => 'CVT Komponen', 'supplier' => 'PT Astra Honda', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'B-020', 'nama_barang' => 'Oli MPX 1 1L', 'stok' => 300, 'satuan' => 'Botol', 'harga_beli' => 48000, 'harga_jual' => 58000, 'kategori' => 'Pelumas', 'supplier' => 'PT Astra Honda', 'tipe_barang' => 'stok'],
        ];

        DB::beginTransaction();
        try {
            $barangInstances = [];
            foreach ($masterBarang as $mb) {
                $barangInstances[$mb['kode_barang']] = Barang::create($mb);
            }

            // Simulasi Periode: Mundur 4 minggu ke belakang
            $startPeriod = Carbon::now()->subWeeks(4)->startOfWeek(Carbon::MONDAY);
            $endPeriod = Carbon::now()->subWeek()->endOfWeek(Carbon::SUNDAY);

            $this->command->info("Menyuntikkan data transaksi murni dari: " . $startPeriod->toDateString() . " s/d " . $endPeriod->toDateString());

            for ($date = clone $startPeriod; $date->lte($endPeriod); $date->addDay()) {
                
                // Transaksi harian berkisar antara 8 hingga 10 nota
                $jumlahTransaksiHariIni = rand(8, 10); 

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

                    // STRATEGI PROBABILITAS ALAMI (Murni Transaksi Penjualan)
                    // Sistem memilih barang yang dibeli berdasarkan kebiasaan nyata di bengkel:
                    
                    // Skenario 1: Barang Fast-Moving (Peluang beli 90% di setiap nota, misal: Busi / Baut)
                    if (rand(1, 100) <= 90) {
                        $fastItems = ['B-006', 'B-007', 'B-008', 'B-009'];
                        $itemsNotaIni[] = $fastItems[array_rand($fastItems)];
                    }

                    // Skenario 2: Barang Medium-Moving (Peluang beli 50% di setiap nota, misal: Kampas Rem / Filter)
                    if (rand(1, 100) <= 50) {
                        $mediumItems = ['B-005', 'B-010', 'B-011', 'B-015', 'B-016', 'B-017', 'B-018'];
                        $itemsNotaIni[] = $mediumItems[array_rand($mediumItems)];
                    }

                    // Skenario 3: Barang Slow-Moving (Peluang beli hanya 15% di setiap nota, misal: Aki / Ban Luar / Gear Set)
                    if (rand(1, 100) <= 15) {
                        $slowItems = ['B-001', 'B-002', 'B-003', 'B-004', 'B-012', 'B-013', 'B-014', 'B-019', 'B-020'];
                        $itemsNotaIni[] = $slowItems[array_rand($slowItems)];
                    }

                    // Hapus duplikasi kode jika tidak sengaja terambil ganda dalam 1 nota
                    $itemsNotaIni = array_unique($itemsNotaIni);

                    // Eksekusi pembuatan data item terjual
                    foreach ($itemsNotaIni as $kodeB) {
                        $barang = $barangInstances[$kodeB];
                        $qtyJual = 1; // Rata-rata dibeli 1 unit per transaksi

                        if ($barang->stok < $qtyJual) continue;

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

                        DB::table('histori_stok')->insert([
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
            $this->command->info(" SEEDER BERHASIL: 20 Master barang sukses dimasukkan.");
            $this->command->info(" Riwayat transaksi murni (tanpa embel-embel klaster manual) sukses dibuat!");
            $this->command->info(" Silakan jalankan KMeansService Anda untuk melihat hasil perhitungan aslinya.");
            $this->command->info("==========================================================================");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("Gagal Eksekusi Seeder: " . $e->getMessage());
        }
    }
}