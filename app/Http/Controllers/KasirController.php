<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class KasirController extends Controller
{
    public function index()
    {
        // Ambil draft beserta relasi details-nya agar bisa di-load oleh JavaScript
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
        $request->validate([
            'metode_pembayaran' => 'required',
        ]);

        // Cek tombol mana yang diklik (Draft atau Cetak)
        $status = $request->submit_type === 'selesai' ? 'selesai' : 'draft';
        $biayaJasa = $request->biaya_jasa_servis ?? 0;

        // Jika form mengirimkan transaksi_id, berarti kita MELANJUTKAN DRAFT
        if ($request->transaksi_id) {
            $transaksi = Transaksi::findOrFail($request->transaksi_id);
            $transaksi->update([
                'jenis_motor' => $request->jenis_motor,
                'total_harga' => $request->total_tagihan ?? 0,
                'biaya_jasa_servis' => $biayaJasa,
                'metode_pembayaran' => $request->metode_pembayaran,
                'status' => $status,
            ]);
            
            // Hapus detail lama, kita akan input ulang dari tabel yang dikirim form
            $transaksi->details()->delete();
        } else {
            // JIKA TRANSAKSI BARU
            $transaksi = Transaksi::create([
                'no_invoice' => 'INV-' . strtoupper(Str::random(5)),
                'user_id' => Auth::id(),
                'jenis_motor' => $request->jenis_motor,
                'total_harga' => $request->total_tagihan ?? 0,
                'biaya_jasa_servis' => $biayaJasa,
                'metode_pembayaran' => $request->metode_pembayaran,
                'status' => $status,
            ]);
        }

        // Simpan Detail Barang & Kurangi Stok
        if ($request->has('items')) {
            foreach ($request->items as $item) {
                DetailTransaksi::create([
                    'transaksi_id' => $transaksi->id,
                    'barang_id' => $item['barang_id'] ?? null,
                    'nama_barang_manual' => $item['nama_barang'] ?? null,
                    'harga_modal' => $item['harga_beli'] ?? 0,
                    'harga_jual_satuan' => $item['harga'],
                    'jumlah' => $item['qty'],
                    'subtotal' => $item['harga'] * $item['qty'],
                ]);

                // LOGIKA PENGURANGAN STOK
                // Pastikan transaksinya 'selesai' dan ini adalah barang dari database (bukan barang manual)
                if ($status === 'selesai' && !empty($item['barang_id'])) {
                    $barang = Barang::find($item['barang_id']);
                    
                    if ($barang) {
                        // Catatan: Pastikan field stok di tabel barang Anda bernama 'stok'.
                        // Jika namanya berbeda (misal: 'jumlah_barang' atau 'qty'), silakan ubah kata 'stok' di bawah ini.
                        $barang->decrement('stok', $item['qty']);
                    }
                }
            }
        }

        // Jika "Cetak", arahkan ke riwayat sambil memicu pop-up print struk
        if ($status === 'selesai') {
            return redirect()->route('kasir.riwayat')
                ->with('cetak_nota', $transaksi->id)
                ->with('success', 'Transaksi Selesai & Berhasil Dicetak');
        }

        // Jika "Simpan Draft", refresh ke halaman kasir awal
        return redirect()->route('kasir.transaksi')->with('success', 'Draft transaksi berhasil disimpan/diperbarui');
    }

    // Fungsi baru untuk layout Nota/Struk
    public function nota($id)
    {
        $transaksi = Transaksi::with('details')->findOrFail($id);
        return view('kasir.nota', compact('transaksi'));
    }
}