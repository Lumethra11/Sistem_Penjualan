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
            'username' => 'adminbengkel',
            'email' => 'admin@aamotor.com',
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
            'name' => 'Siti Kasir Utama',
            'username' => 'kasirbengkel',
            'email' => 'kasir@aamotor.com',
            'nama_toko' => null,
            'no_telp' => '089876543210',
            'alamat' => null,
            'is_active' => true,
            'can_input_manual_barang' => false,
            'password' => Hash::make('password123'),
        ]);

        // 3. MASTER DATA BARANG (STOK REALSITIS)
        $masterBarang = [
            ['kode_barang' => 'BAUT01', 'nama_barang' => 'Baut Blok Mesin Grand 12mm', 'stok' => 250, 'satuan' => 'Pcs', 'harga_beli' => 1500, 'harga_jual' => 3000, 'kategori' => 'Mur & Baut', 'supplier' => 'Putra Otomotif', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'BAUT02', 'nama_barang' => 'Baut Knalpot Honda Ring 14', 'stok' => 180, 'satuan' => 'Pcs', 'harga_beli' => 2000, 'harga_jual' => 4000, 'kategori' => 'Mur & Baut', 'supplier' => 'Putra Otomotif', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'WSH001', 'nama_barang' => 'Ring Kuningan Baut Oli 12mm', 'stok' => 300, 'satuan' => 'Pcs', 'harga_beli' => 500, 'harga_jual' => 1500, 'kategori' => 'Suku Cadang', 'supplier' => 'Putra Otomotif', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'OLI001', 'nama_barang' => 'Oli MPX 2 0.8L Matik', 'stok' => 45, 'satuan' => 'Botol', 'harga_beli' => 45000, 'harga_jual' => 55000, 'kategori' => 'Pelumas', 'supplier' => 'PT Astra Honda', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'OLI002', 'nama_barang' => 'Oli SPX 2 1L Matik Super', 'stok' => 35, 'satuan' => 'Botol', 'harga_beli' => 58000, 'harga_jual' => 70000, 'kategori' => 'Pelumas', 'supplier' => 'PT Astra Honda', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'BSI001', 'nama_barang' => 'Busi Denso U27EPR-9 Injection', 'stok' => 80, 'satuan' => 'Pcs', 'harga_beli' => 13000, 'harga_jual' => 20000, 'kategori' => 'Pengapian', 'supplier' => 'Denso Indonesia', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'OLI003', 'nama_barang' => 'Oli Yamalube Matic M3', 'stok' => 30, 'satuan' => 'Botol', 'harga_beli' => 43000, 'harga_jual' => 53000, 'kategori' => 'Pelumas', 'supplier' => 'Yamaha Motor Parts', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'BKT001', 'nama_barang' => 'Kampas Rem Depan Beat FI Nissin', 'stok' => 40, 'satuan' => 'Pcs', 'harga_beli' => 25000, 'harga_jual' => 35000, 'kategori' => 'Pengereman', 'supplier' => 'PT Astra Honda', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'BKT002', 'nama_barang' => 'Kampas Rem Belakang Vario CBS', 'stok' => 35, 'satuan' => 'Pcs', 'harga_beli' => 28000, 'harga_jual' => 40000, 'kategori' => 'Pengereman', 'supplier' => 'PT Astra Honda', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'RLR001', 'nama_barang' => 'Roller Set Beat FI Honda Genuine', 'stok' => 30, 'satuan' => 'Set', 'harga_beli' => 35000, 'harga_jual' => 50000, 'kategori' => 'CVT Komponen', 'supplier' => 'PT Astra Honda', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'FLT001', 'nama_barang' => 'Filter Udara Vario 150 Esp', 'stok' => 25, 'satuan' => 'Pcs', 'harga_beli' => 32000, 'harga_jual' => 45000, 'kategori' => 'Filter', 'supplier' => 'PT Astra Honda', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'BAN001', 'nama_barang' => 'Ban Luar FDR Sport XR Evo 80/90', 'stok' => 20, 'satuan' => 'Pcs', 'harga_beli' => 160000, 'harga_jual' => 205000, 'kategori' => 'Ban', 'supplier' => 'FDR Jatim', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'AKI001', 'nama_barang' => 'Aki GS Astra YTZ6V Dry Premium', 'stok' => 15, 'satuan' => 'Pcs', 'harga_beli' => 210000, 'harga_jual' => 265000, 'kategori' => 'Kelistrikan', 'supplier' => 'GS Astra', 'tipe_barang' => 'stok'],
        ];

        DB::beginTransaction();
        try {
            $barangInstances = [];
            foreach ($masterBarang as $mb) {
                $barangInstances[$mb['kode_barang']] = Barang::create($mb);
            }

            // Pengelompokan manual untuk mengarahkan distribusi centroid
            $larisKodes = ['BAUT01', 'BAUT02', 'WSH001', 'OLI001', 'OLI002', 'BSI001']; 
            $sedangKodes = ['OLI003', 'BKT001', 'BKT002', 'RLR001', 'FLT001'];
            $kurangLarisKodes = ['BAN001', 'AKI001'];

            // LOGIKA DINAMIS: Mencari hari Senin s/d Ahad pada MINGGU LALU (W3)
            $startWeekLalu = Carbon::now()->subWeek()->startOfWeek(Carbon::MONDAY);
            $endWeekLalu = Carbon::now()->subWeek()->endOfWeek(Carbon::SUNDAY);

            $this->command->info("Menyuntikkan data berturut-turut dari tanggal: " . $startWeekLalu->toDateString() . " s/d " . $endWeekLalu->toDateString());

            // Loop berjalan setiap hari dari Senin sampai Ahad (7 Hari berturut-turut)
            for ($date = clone $startWeekLalu; $date->lte($endWeekLalu); $date->addDay()) {
                
                // Setiap harinya terjadi antara 2 sampai 4 transaksi acak di bengkel
                $jumlahTransaksiHariIni = rand(2, 4);

                for ($inv = 1; $inv <= $jumlahTransaksiHariIni; $inv++) {
                    $waktuSpesifik = clone $date;
                    $waktuSpesifik->setTime(rand(8, 17), rand(10, 59)); // Jam operasional bengkel 08:00 - 17:00

                    $noInvoice = 'INV-' . $waktuSpesifik->format('ymd') . '-' . str_pad($inv, 2, '0', STR_PAD_LEFT);
                    
                    $transaksi = Transaksi::create([
                        'no_invoice'        => $noInvoice,
                        'user_id'           => $kasir->id,
                        'jenis_motor'       => ['Beat FI', 'Vario 125', 'Scoopy ESP', 'Mio M3', 'Supra X'][rand(0, 4)],
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

                    // 1. Pasti membeli barang kategori Laris
                    $kodeLaris = $larisKodes[array_rand($larisKodes)];
                    $qtyLaris = (strpos($kodeLaris, 'BAUT') !== false || strpos($kodeLaris, 'WSH') !== false) ? rand(5, 12) : rand(1, 2);
                    $itemsNotaIni[] = ['barang' => $barangInstances[$kodeLaris], 'qty' => $qtyLaris];

                    // 2. Peluang 70% membeli barang kategori Sedang
                    if (rand(1, 100) <= 70) {
                        $kodeSedang = $sedangKodes[array_rand($sedangKodes)];
                        $itemsNotaIni[] = ['barang' => $barangInstances[$kodeSedang], 'qty' => rand(1, 2)];
                    }

                    // 3. Peluang 25% membeli barang kategori Kurang Laris (seperti Aki/Ban yang jarang diganti)
                    if (rand(1, 100) <= 25) {
                        $kodeKurang = $kurangLarisKodes[array_rand($kurangLarisKodes)];
                        $itemsNotaIni[] = ['barang' => $barangInstances[$kodeKurang], 'qty' => 1];
                    }

                    // Pemrosesan item transaksi
                    foreach ($itemsNotaIni as $item) {
                        $barang = $item['barang'];
                        $qtyJual = $item['qty'];

                        if ($barang->stok < $qtyJual) $qtyJual = $barang->stok;
                        if ($qtyJual <= 0) continue;

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
                            'keterangan'        => 'Penjualan Berturut Nota ' . $noInvoice,
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
            $this->command->info(" SEEDER BERHASIL: Transaksi berturut-turut 7 hari penuh di W3 sukses!");
            $this->command->info(" Silakan buka/refresh Dashboard utama untuk memicu penyimpanan riwayat.");
            $this->command->info("==========================================================================");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("Gagal Eksekusi Seeder: " . $e->getMessage());
        }
    }
}