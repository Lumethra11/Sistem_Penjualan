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
use Illuminate\Support\Str;

class TestingKMeansSeeder extends Seeder
{
    public function run(): void
    {
        // 1. CLEAR DATA LAMA UNTUK TESTING FRESH SEEDING
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DetailTransaksi::truncate();
        Transaksi::truncate();
        DB::table('histori_stok')->truncate();
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

        // 3. MASTER DATA: 20 BARANG DENGAN STOK AWAL LEBIH AMAN & BESAR
        $masterBarang = [
            ['kode_barang' => 'OLI001', 'nama_barang' => 'Oli MPX 2 0.8L Matik', 'stok' => 50, 'satuan' => 'Botol', 'harga_beli' => 45000, 'harga_jual' => 55000, 'kategori' => 'Pelumas', 'supplier' => 'PT Astra Honda', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'OLI002', 'nama_barang' => 'Oli SPX 2 1L Matik Super', 'stok' => 45, 'satuan' => 'Botol', 'harga_beli' => 58000, 'harga_jual' => 70000, 'kategori' => 'Pelumas', 'supplier' => 'PT Astra Honda', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'OLI003', 'nama_barang' => 'Oli Yamalube Matic M3', 'stok' => 45, 'satuan' => 'Botol', 'harga_beli' => 43000, 'harga_jual' => 53000, 'kategori' => 'Pelumas', 'supplier' => 'Yamaha Motor Parts', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'BSI001', 'nama_barang' => 'Busi Denso U27EPR-9 Injection', 'stok' => 60, 'satuan' => 'Pcs', 'harga_beli' => 13000, 'harga_jual' => 20000, 'kategori' => 'Pengapian', 'supplier' => 'Denso Indonesia', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'BKT001', 'nama_barang' => 'Kampas Rem Depan Beat FI Nissin', 'stok' => 50, 'satuan' => 'Pcs', 'harga_beli' => 25000, 'harga_jual' => 35000, 'kategori' => 'Pengereman', 'supplier' => 'PT Astra Honda', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'BKT002', 'nama_barang' => 'Kampas Rem Belakang Vario CBS', 'stok' => 50, 'satuan' => 'Pcs', 'harga_beli' => 28000, 'harga_jual' => 40000, 'kategori' => 'Pengereman', 'supplier' => 'PT Astra Honda', 'tipe_barang' => 'stok'],
            
            ['kode_barang' => 'BAN001', 'nama_barang' => 'Ban Luar FDR Sport XR Evo 80/90-14', 'stok' => 120, 'satuan' => 'Pcs', 'harga_beli' => 160000, 'harga_jual' => 205000, 'kategori' => 'Ban', 'supplier' => 'FDR Jatim', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'BAN002', 'nama_barang' => 'Ban Luar IRC Exato 90/90-14 Tubeless', 'stok' => 110, 'satuan' => 'Pcs', 'harga_beli' => 190000, 'harga_jual' => 240000, 'kategori' => 'Ban', 'supplier' => 'IRC Nusantara', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'AKI001', 'nama_barang' => 'Aki GS Astra YTZ6V Dry Premium', 'stok' => 85, 'satuan' => 'Pcs', 'harga_beli' => 210000, 'harga_jual' => 265000, 'kategori' => 'Kelistrikan', 'supplier' => 'GS Astra', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'AKI002', 'nama_barang' => 'Aki Motobatt Gel MTZ5S Waterproof', 'stok' => 90, 'satuan' => 'Pcs', 'harga_beli' => 185000, 'harga_jual' => 230000, 'kategori' => 'Kelistrikan', 'supplier' => 'Motobatt Indonesia', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'VBL001', 'nama_barang' => 'V-Belt Kit Honda Scoopy FI Vanbelt', 'stok' => 75, 'satuan' => 'Set', 'harga_beli' => 110000, 'harga_jual' => 150000, 'kategori' => 'CVT Komponen', 'supplier' => 'PT Astra Honda', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'VBL002', 'nama_barang' => 'V-Belt Kit Yamaha NMAX Old', 'stok' => 70, 'satuan' => 'Set', 'harga_beli' => 135000, 'harga_jual' => 180000, 'kategori' => 'CVT Komponen', 'supplier' => 'Yamaha Motor Parts', 'tipe_barang' => 'stok'],

            ['kode_barang' => 'RLR001', 'nama_barang' => 'Roller Set Beat FI Honda Genuine', 'stok' => 45, 'satuan' => 'Set', 'harga_beli' => 35000, 'harga_jual' => 50000, 'kategori' => 'CVT Komponen', 'supplier' => 'PT Astra Honda', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'FLT001', 'nama_barang' => 'Filter Udara Vario 150 Esp', 'stok' => 50, 'satuan' => 'Pcs', 'harga_beli' => 32000, 'harga_jual' => 45000, 'kategori' => 'Filter', 'supplier' => 'PT Astra Honda', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'FLT002', 'nama_barang' => 'Filter Udara Mio M3 Sporty', 'stok' => 48, 'satuan' => 'Pcs', 'harga_beli' => 30000, 'harga_jual' => 42000, 'kategori' => 'Filter', 'supplier' => 'Yamaha Motor Parts', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'LMP001', 'nama_barang' => 'Lampu Depan LED Tyto H6 Putih', 'stok' => 55, 'satuan' => 'Pcs', 'harga_beli' => 22000, 'harga_jual' => 35000, 'kategori' => 'Penerangan', 'supplier' => 'Tyto Motor Indonesia', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'RNT001', 'nama_barang' => 'Gir Paket Paket Chain Kit Supra X', 'stok' => 38, 'satuan' => 'Set', 'harga_beli' => 140000, 'harga_jual' => 185000, 'kategori' => 'Penggerak', 'supplier' => 'PT Astra Honda', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'SKR001', 'nama_barang' => 'Shockbreaker Belakang Kayaba Vario', 'stok' => 32, 'satuan' => 'Pcs', 'harga_beli' => 195000, 'harga_jual' => 240000, 'kategori' => 'Suspensi', 'supplier' => 'Kayaba Indonesia', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'CBL001', 'nama_barang' => 'Kabel Gas Honda Vario KZR Ori', 'stok' => 42, 'satuan' => 'Pcs', 'harga_beli' => 25000, 'harga_jual' => 38000, 'kategori' => 'Kabel', 'supplier' => 'PT Astra Honda', 'tipe_barang' => 'stok'],
            ['kode_barang' => 'CBL002', 'nama_barang' => 'Kabel Kopling Sport Tiger Revo', 'stok' => 35, 'satuan' => 'Pcs', 'harga_beli' => 30000, 'harga_jual' => 45000, 'kategori' => 'Kabel', 'supplier' => 'PT Astra Honda', 'tipe_barang' => 'stok'],
        ];

        $barangInstances = [];
        foreach ($masterBarang as $mb) {
            $barangInstances[$mb['kode_barang']] = Barang::create($mb);
        }

        $fastMovingKodes = ['OLI001', 'OLI002', 'OLI003', 'BSI001', 'BKT001', 'BKT002'];
        $slowMovingKodes = ['BAN001', 'BAN002', 'AKI001', 'AKI002', 'VBL001', 'VBL002', 'RLR001', 'FLT001', 'FLT002', 'LMP001', 'RNT001', 'SKR001', 'CBL001', 'CBL002'];

        // 4. GENERATE TEPAT 5 TRANSAKSI SEHARI SELAMA 7 HARI (35 NOTA REAL)
        for ($hari = 6; $hari >= 0; $hari--) {
            $tanggalTransaksi = Carbon::now()->subDays($hari);

            for ($inv = 1; $inv <= 5; $inv++) {
                $waktuSpesifik = clone $tanggalTransaksi;
                $waktuSpesifik->setTime(rand(8, 16), rand(10, 59));

                $noInvoice = 'INV-' . $waktuSpesifik->format('ymd') . str_pad($inv, 3, '0', STR_PAD_LEFT);
                
                $transaksi = Transaksi::create([
                    'no_invoice'        => $noInvoice,
                    'user_id'           => $kasir->id,
                    'jenis_motor'       => ['Beat FI', 'Vario 125', 'Scoopy ESP', 'Mio M3', 'Supra X 125'][rand(0, 4)],
                    'biaya_jasa_servis' => [0, 25000, 35000][rand(0, 2)],
                    'total_harga'       => 0,
                    'metode_pembayaran' => ['Tunai', 'Transfer', 'QRIS'][rand(0, 2)],
                    'status'            => 'selesai',
                    'created_at'        => $waktuSpesifik,
                    'updated_at'        => $waktuSpesifik,
                ]);

                $subtotalInvoice = 0;
                $itemsNotaIni = [];

                // Ambil barang Fast-Moving acak
                $kodeFastUji = $fastMovingKodes[array_rand($fastMovingKodes)];
                $itemsNotaIni[] = ['barang' => $barangInstances[$kodeFastUji], 'qty' => rand(1, 2)];

                if (rand(0, 1) === 1) {
                    $kodeSlowUji = $slowMovingKodes[array_rand($slowMovingKodes)];
                    $itemsNotaIni[] = ['barang' => $barangInstances[$kodeSlowUji], 'qty' => 1];
                }

                foreach ($itemsNotaIni as $item) {
                    $barang = $item['barang'];
                    $qtyJual = $item['qty'];

                    // KUNCI AMAN: Cek apakah stok mencukupi. Jika sisa stok lebih kecil dari kuantitas jual, pangkas penjualan senilai sisa stok yang ada
                    if ($barang->stok < $qtyJual) {
                        $qtyJual = $barang->stok;
                    }

                    // Jika stok benar-benar 0 (Habis), batalkan memasukkan barang ke detail nota
                    if ($qtyJual <= 0) {
                        continue;
                    }

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

                    // Potong kuantitas fisik (Aman, tidak akan pernah bernilai minus lagi)
                    $barang->decrement('stok', $qtyJual);

                    DB::table('histori_stok')->insert([
                        'barang_id'         => $barang->id,
                        'jenis_pergerakan'  => 'keluar',
                        'jumlah'            => $qtyJual,
                        'sisa_stok_saat_ini'=> $barang->stok,
                        'keterangan'        => 'Penjualan Selesai Nota ' . $noInvoice,
                        'created_at'        => $waktuSpesifik,
                        'updated_at'        => $waktuSpesifik,
                    ]);

                    $subtotalInvoice += $subtotalItem;
                }

                $totalAkhirInvoice = $subtotalInvoice + $transaksi->biaya_jasa_servis;
                $transaksi->update(['total_harga' => $totalAkhirInvoice]);
            }
        }

        DB::commit();
    }
}