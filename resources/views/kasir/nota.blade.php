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
            position: relative;
        }
        .center { text-align: center; }
        .text-right { text-align: right; }
        .line { border-bottom: 1px dashed #000; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; position: relative; z-index: 2; }
        td, th { padding: 4px 0; vertical-align: top; }
        th { text-align: left; }
        
        .header-section {
            position: relative;
            z-index: 2;
        }
        .header-section p {
            margin: 4px 0 0 0;
            font-size: 13px;
        }

        /* WATERMARK LOGO GRAFIS */
        .watermark-img {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 180px; 
            height: 180px;
            object-fit: contain;
            opacity: 0.08; /* Transparansi tipis (8%) agar teks nota tajam */
            z-index: 1;
            pointer-events: none;
        }

        /* WATERMARK TEKS CADANGAN (Jika foto tidak ada) */
        .watermark-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg); /* Dimiringkan agar estetik */
            font-size: 24px;
            font-weight: bold;
            text-transform: uppercase;
            color: rgba(0, 0, 0, 0.06); /* Sangat samar */
            text-align: center;
            width: 280px;
            z-index: 1;
            pointer-events: none;
            white-space: normal;
            word-wrap: break-word;
        }

        @media print {
            body { width: 100%; margin: 0; position: relative; }
            .no-print { display: none; }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        }
    </style>
</head>
<body onload="window.print()">

    @php
        $userAktif = $transaksi->user;
        $namaToko = null;
        $alamatToko = null;
        $fotoAdmin = null;
        
        // JIKA KASIR YANG MEMBUAT: Tarik data dari Admin di atasnya
        if ($userAktif && $userAktif->role === 'kasir' && $userAktif->admin) {
            $namaToko = $userAktif->admin->nama_toko;
            $alamatToko = $userAktif->admin->alamat;
            $fotoAdmin = $userAktif->admin->foto_profil; 
        } else if ($userAktif && $userAktif->role === 'admin') {
            // JIKA ADMIN YANG MEMBUAT LANGSUNG
            $namaToko = $userAktif->nama_toko;
            $alamatToko = $userAktif->alamat;
            $fotoAdmin = $userAktif->foto_profil; 
        }

        // LOGIKA AMBIL NOMINAL BAYAR & KEMBALIAN
        $nominalBayar = $transaksi->bayar ?? $transaksi->diterima ?? request('nominal_bayar', 0);
        $kembalian = $transaksi->kembalian ?? request('kembalian', 0);

        if ($transaksi->metode_pembayaran === 'Tunai' && $nominalBayar == 0) {
            $nominalBayar = session('nominal_bayar') ?? 0;
            $kembalian = session('kembalian') ?? 0;
        }
    @endphp

    {{-- LOGIKA WATERMARK DINAMIS SISTEM PUBLIK --}}
    @if(!empty($fotoAdmin) && file_exists(public_path('uploads/profile/' . $fotoAdmin)))
        {{-- JIKA FOTO PROFIL ADA, GUNAKAN WATERMARK GAMBAR (Mengarah ke folder uploads/profile) --}}
        <img src="{{ asset('uploads/profile/' . $fotoAdmin) }}" class="watermark-img" alt="Logo">
    @elseif(!empty($namaToko))
        {{-- JIKA FOTO TIDAK ADA, GENERATE WATERMARK TEKS NAMA TOKO --}}
        <div class="watermark-text">{{ $namaToko }}</div>
    @endif

    {{-- HEADER NOTA --}}
    <div class="center header-section">
        <h2>{{ $namaToko ?? '-' }}</h2>
        @if(!empty($alamatToko))
            <p>{!! nl2br(e($alamatToko)) !!}</p>
        @endif
    </div>

    <div class="line"></div>

    {{-- METADATA INFORMASI NOTA --}}
    <table>
        <tr>
            <td style="width: 70px;">Inv</td>
            <td>: {{ $transaksi->no_invoice }}</td>
        </tr>
        <tr>
            <td>Tgl</td>
            <td>: {{ $transaksi->created_at->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td>Mtr</td>
            <td>: {{ $transaksi->jenis_motor ?? 'Motor' }} ({{ $transaksi->nomor_kendaraan ?? '-' }})</td>
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

    {{-- LIST ITEM BARANG --}}
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

    <div class="center" style="position: relative; z-index: 2;">
        <p>Terima kasih atas kunjungan Anda!</p>
    </div>

    <button class="no-print" onclick="window.print()" style="margin-top: 25px; width: 100%; padding: 10px; font-weight: bold; cursor: pointer;">Print Ulang Nota</button>

</body>
</html>