<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Nota - {{ $transaksi->no_invoice }}</title>
    <style>
        body { 
            font-family: 'Courier New', Courier, monospace; 
            font-size: 14px; 
            width: 300px; 
            margin: 0 auto; 
            color: #000; 
        }
        .center { text-align: center; }
        .text-right { text-align: right; }
        .line { border-bottom: 1px dashed #000; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; }
        td, th { padding: 4px 0; vertical-align: top; }
        th { text-align: left; }
        
        .header-section p {
            margin: 4px 0 0 0;
            font-size: 13px;
        }
        @media print {
            body { width: 100%; margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">

    @php
        $userAktif = $transaksi->user;
        
        // JIKA KASIR YANG MEMBUAT: Tarik data Toko & Alamat dari Admin di atasnya
        if ($userAktif && $userAktif->role === 'kasir' && $userAktif->admin) {
            $namaToko = $userAktif->admin->nama_toko;
            $alamatToko = $userAktif->admin->alamat;
        } else {
            // JIKA ADMIN YANG MEMBUAT LANGSUNG
            $namaToko = $userAktif->nama_toko ?? 'BENGKEL AA MOTOR';
            $alamatToko = $userAktif->alamat ?? '';
        }

        // LOGIKA AMBIL NOMINAL BAYAR & KEMBALIAN
        // Memeriksa apakah kolom bayar/kembalian ada di database, jika tidak ada pakai fallback request dinamis
        $nominalBayar = $transaksi->bayar ?? $transaksi->diterima ?? request('nominal_bayar', 0);
        $kembalian = $transaksi->kembalian ?? request('kembalian', 0);

        // Jika data di atas masih 0 dan transaksinya tunai, kita coba kalkulasi atau ambil dari input halaman sebelumnya
        if ($transaksi->metode_pembayaran === 'Tunai' && $nominalBayar == 0) {
            // Mengambil state dari pengisian form jika dilemparkan via session/request
            $nominalBayar = session('nominal_bayar') ?? 0;
            $kembalian = session('kembalian') ?? 0;
        }
    @endphp

    {{-- HEADER NOTA KEMBALI KE DESAIN AWAL YANG COCOK DAN BAGUS --}}
    <div class="center header-section">
        <h2>{{ $namaToko }}</h2>
        <p>{!! nl2br(e($alamatToko)) !!}</p>
    </div>

    <div class="line"></div>

    {{-- METADATA INFORMASI NOTA KASIR --}}
    <table>
        <tr>
            <td>Inv</td>
            <td>: {{ $transaksi->no_invoice }}</td>
        </tr>
        <tr>
            <td>Tgl</td>
            <td>: {{ $transaksi->created_at->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td>Mtr</td>
            <td>: {{ $transaksi->jenis_motor ?? '-' }}</td>
        </tr>
        <tr>
            <td>Metode</td>
            <td>: {{ $transaksi->metode_pembayaran }}</td>
        </tr>
        <tr>
            <td>Kasir</td>
            <td>: {{ $userAktif->name ?? 'Kasir' }}</td>
        </tr>
        @if(!empty($userAktif->no_telp))
        <tr>
            <td>Telp</td>
            <td>: {{ $userAktif->no_telp }}</td>
        </tr>
        @endif
    </table>

    <div class="line"></div>

    {{-- LIST ITEM BARANG BELANJAAN --}}
    <table>
        @foreach($transaksi->details as $item)
        <tr>
            <td colspan="2">{{ $item->barang ? $item->barang->nama_barang : $item->nama_barang_manual }}</td>
        </tr>
        <tr>
            <td>{{ $item->jumlah }} x {{ number_format($item->harga_jual_satuan, 0, ',', '.') }}</td>
            <td class="text-right">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
        </tr>
        @endforeach
    </table>

    <div class="line"></div>

    {{-- RINGKASAN PEMBAYARAN FINAL --}}
    <table>
        @if($transaksi->biaya_jasa_servis > 0)
        <tr>
            <td>Biaya Jasa Servis</td>
            <td class="text-right">{{ number_format($transaksi->biaya_jasa_servis, 0, ',', '.') }}</td>
        </tr>
        @endif
        <tr>
            <th>TOTAL TAGIHAN</th>
            <th class="text-right">Rp {{ number_format($transaksi->total_harga, 0, ',', '.') }}</th>
        </tr>

        {{-- TAMBAHAN FITUR: Menampilkan Cash/Tunai, Bayar, dan Kembalian --}}
        @if($transaksi->metode_pembayaran === 'Tunai' && $nominalBayar > 0)
        <tr>
            <td>Dibayar (Tunai)</td>
            <td class="text-right">{{ number_format($nominalBayar, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Kembalian</td>
            <td class="text-right">{{ number_format($kembalian, 0, ',', '.') }}</td>
        </tr>
        @endif
    </table>

    <div class="line"></div>

    <div class="center">
        <p>Terima kasih atas kunjungan Anda!</p>
    </div>

    <button class="no-print" onclick="window.print()" style="margin-top: 25px; width: 100%; padding: 10px; font-weight: bold; cursor: pointer;">Print Ulang Nota</button>

</body>
</html>