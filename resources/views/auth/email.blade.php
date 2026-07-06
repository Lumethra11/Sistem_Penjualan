<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Sistem Penjualan</title>
</head>

<body style="margin:0;padding:0;background:#f4f6f9;font-family:Arial,Helvetica,sans-serif;color:#374151;">

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
        style="padding:40px 15px;background:#f4f6f9;">
        <tr>
            <td align="center">

                <table role="presentation" width="600" cellpadding="0" cellspacing="0"
                    style="max-width:600px;background:#ffffff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;">

                    <tr>
                        <td align="center"
                            style="padding:32px;background:#2563eb;color:#ffffff;">

                            <h1 style="margin:0;font-size:28px;font-weight:bold;">
                                Sistem Penjualan
                            </h1>

                            <p style="margin:10px 0 0;font-size:15px;color:#dbeafe;">
                                Reset Password Akun
                            </p>

                        </td>
                    </tr>

                    <tr>
                        <td style="padding:40px;">

                            <h2 style="margin-top:0;color:#111827;font-size:22px;">
                                Halo,
                            </h2>

                            <p style="font-size:16px;line-height:1.8;color:#4b5563;">
                                Kami menerima permintaan untuk mengatur ulang password akun
                                <strong>Sistem Penjualan</strong>.
                            </p>

                            <p style="font-size:16px;line-height:1.8;color:#4b5563;">
                                Klik tombol di bawah ini untuk membuat password baru.
                            </p>

                            <table role="presentation" align="center" cellpadding="0" cellspacing="0"
                                style="margin:35px auto;">
                                <tr>
                                    <td bgcolor="#2563eb" style="border-radius:8px;">

                                        <a href="{{ $url }}"
                                            style="
                                                display:inline-block;
                                                padding:15px 34px;
                                                color:#ffffff;
                                                text-decoration:none;
                                                font-size:16px;
                                                font-weight:bold;
                                            ">
                                            Reset Password
                                        </a>

                                    </td>
                                </tr>
                            </table>

                            <hr style="border:none;border-top:1px solid #e5e7eb;margin:35px 0;">

                            <p style="font-size:14px;color:#6b7280;line-height:1.7;">
                                Jika tombol di atas tidak dapat digunakan, salin dan buka tautan berikut melalui browser:
                            </p>

                            <div
                                style="
                                    background:#f9fafb;
                                    border:1px solid #d1d5db;
                                    padding:14px;
                                    border-radius:8px;
                                    word-break:break-all;
                                    font-size:13px;
                                    color:#2563eb;
                                ">
                                {{ $url }}
                            </div>

                            <p style="margin-top:35px;font-size:14px;line-height:1.8;color:#6b7280;">
                                Demi keamanan akun Anda, tautan ini **hanya berlaku selama 60 menit** sejak email ini dikirimkan.
                                Jika Anda tidak pernah meminta reset password, abaikan email ini dengan aman.
                                Password Anda tidak akan berubah.
                            </p>

                            <p style="margin-top:35px;font-size:15px;color:#374151;">
                                Terima kasih,<br>
                                <strong>Tim Sistem Penjualan</strong>
                            </p>

                        </td>
                    </tr>

                    <tr>
                        <td align="center"
                            style="padding:24px;background:#f9fafb;border-top:1px solid #e5e7eb;">

                            <p style="margin:0;font-size:13px;color:#6b7280;">
                                Email ini dikirim secara otomatis. Mohon tidak membalas email ini.
                            </p>

                            <p style="margin-top:10px;font-size:12px;color:#9ca3af;">
                                © {{ date('Y') }} Sistem Penjualan. Seluruh Hak Cipta Dilindungi.
                            </p>

                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>

</html>