<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Sistem Laboratorium Lingkungan Terpadu</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; color: #1e293b; line-height: 1.6;">
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f1f5f9; padding: 30px 15px;">
        <tr>
            <td align="center">
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 580px; background-color: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);">
                    <!-- Header with Logo -->
                    <tr>
                        <td align="center" style="padding: 36px 30px 24px; border-bottom: 1px solid #f1f5f9;">
                            <!-- Text-based Branded Logo -->
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" align="center" style="margin: 0 auto;">
                                <tr>
                                    <td align="center" valign="middle" style="padding: 12px 22px; background-color: #065f46; border-radius: 12px;">
                                        <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td valign="middle" style="font-size: 24px; line-height: 1; padding-right: 12px;">🔬</td>
                                                <td valign="middle" style="text-align: left;">
                                                    <div style="font-size: 14px; font-weight: 800; color: #ffffff; letter-spacing: 1.5px; text-transform: uppercase; font-family: 'Segoe UI', Arial, sans-serif; line-height: 1.2;">LABORATORIUM</div>
                                                    <div style="font-size: 11px; font-weight: 600; color: #a7f3d0; letter-spacing: 0.8px; text-transform: uppercase; font-family: 'Segoe UI', Arial, sans-serif; line-height: 1.3;">LINGKUNGAN TERPADU</div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            <h1 style="margin: 16px 0 0; font-size: 16px; font-weight: 700; color: #0f172a; letter-spacing: -0.3px;">
                                Sistem Laboratorium Lingkungan Terpadu
                            </h1>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 32px 36px 24px;">
                            <p style="margin: 0 0 16px; font-size: 15px; color: #1e293b; font-weight: 600;">
                                Halo{{ !empty($email ?? $user?->email) ? ' ' . ($email ?? $user?->email) : '' }},
                            </p>
                            <p style="margin: 0 0 14px; font-size: 14px; color: #334155; line-height: 1.6;">
                                Kami menerima permintaan untuk mengatur ulang password akun Anda pada Sistem Laboratorium Lingkungan Terpadu.
                            </p>
                            <p style="margin: 0 0 26px; font-size: 14px; color: #334155; line-height: 1.6;">
                                Silakan klik tombol di bawah untuk membuat password baru.
                            </p>

                            <!-- Reset Button -->
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 28px;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $resetUrl }}" target="_blank" style="display: inline-block; background-color: #059669; background-image: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #ffffff !important; text-decoration: none; font-size: 14px; font-weight: 700; padding: 13px 32px; border-radius: 10px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.35); text-align: center;">
                                            Atur Ulang Password
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 0 0 14px; font-size: 13px; color: #64748b; line-height: 1.5;">
                                Link pengaturan ulang password ini akan kedaluwarsa dalam 60 menit.
                            </p>
                            <p style="margin: 0 0 28px; font-size: 13px; color: #64748b; line-height: 1.5;">
                                Jika Anda tidak meminta pengaturan ulang password, Anda dapat mengabaikan email ini.
                            </p>

                            <!-- Divider -->
                            <div style="border-top: 1px dashed #e2e8f0; margin-bottom: 24px;"></div>

                            <!-- Signoff -->
                            <p style="margin: 0 0 4px; font-size: 14px; color: #334155;">
                                Hormat kami,
                            </p>
                            <p style="margin: 0; font-size: 14px; font-weight: 700; color: #065f46;">
                                Tim Sistem Laboratorium Lingkungan Terpadu
                            </p>
                        </td>
                    </tr>

                    <!-- Fallback URL & Footer -->
                    <tr>
                        <td style="background-color: #f8fafc; padding: 20px 36px; border-top: 1px solid #f1f5f9; font-size: 12px; color: #94a3b8; line-height: 1.5;">
                            <p style="margin: 0 0 8px; word-break: break-all;">
                                Jika Anda mengalami kendala saat mengeklik tombol di atas, salin dan tempel tautan berikut ke browser Anda:<br>
                                <a href="{{ $resetUrl }}" style="color: #059669; text-decoration: underline;">{{ $resetUrl }}</a>
                            </p>
                            <p style="margin: 8px 0 0; text-align: center; color: #cbd5e1; font-size: 11px;">
                                &copy; {{ date('Y') }} Sistem Laboratorium Lingkungan Terpadu. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
