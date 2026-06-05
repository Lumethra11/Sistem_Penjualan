<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Nota - {{ $transaksi->no_invoice }}</title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; font-size: 14px; width: 300px; margin: 0 auto; color: #000; }
        .center { text-align: center; }
        .line { border-bottom: 1px dashed #000; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; }
        td, th { padding: 4px 0; vertical-align: top; }
        .text-right { text-align: right; }
        @media print {
            body { width: 100%; margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="center">
        <h2>BENGKEL KITA</h2>
        <p>Jl. Contoh Jalan No.123<br>Telp: 0812-3456-7890</p>
    </div>

    <div class="line"></div>

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
    </table>

    <div class="line"></div>

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

    <table>
        <tr>
            <td>Biaya Jasa Servis</td>
            <td class="text-right">{{ number_format($transaksi->biaya_jasa_servis, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>TOTAL</th>
            <th class="text-right">Rp. {{ number_format($transaksi->total_harga, 0, ',', '.') }}</th>
        </tr>
    </table>

    <div class="line"></div>

    <div class="center">
        <p>Terima kasih atas kunjungan Anda!</p>
    </div>

    <button class="no-print" onclick="window.print()" style="margin-top: 20px; width: 100%; padding: 10px;">Print Ulang</button>

</body>
</html>