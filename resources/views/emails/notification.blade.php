<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $notification->title }}</title>
    <style>
        body {
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
        }
        .header {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            padding: 32px 30px;
            text-align: center;
            color: #ffffff;
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        .header p {
            margin: 6px 0 0;
            font-size: 13px;
            color: #d1fae5;
        }
        .body-content {
            padding: 32px 30px;
        }
        .greeting {
            font-size: 16px;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 16px;
        }
        .card-alert {
            background-color: #f0fdf4;
            border-left: 4px solid #10b981;
            padding: 18px 20px;
            border-radius: 8px;
            margin-bottom: 24px;
        }
        .alert-title {
            font-size: 16px;
            font-weight: 700;
            color: #065f46;
            margin: 0 0 6px 0;
        }
        .alert-message {
            font-size: 14px;
            color: #1e293b;
            margin: 0;
            line-height: 1.5;
        }
        .action-container {
            text-align: center;
            margin: 32px 0 20px;
        }
        .btn-action {
            display: inline-block;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #ffffff !important;
            text-decoration: none;
            padding: 12px 28px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }
        .meta-info {
            font-size: 12px;
            color: #64748b;
            margin-top: 24px;
            border-top: 1px dashed #e2e8f0;
            padding-top: 16px;
        }
        .footer {
            background-color: #f8fafc;
            padding: 24px 30px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
            border-top: 1px solid #f1f5f9;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔬 {{ $appName }}</h1>
            <p>Sistem Informasi & Manajemen Laboratorium Lingkungan</p>
        </div>

        <div class="body-content">
            <div class="greeting">
                Halo, {{ $user->nama ?? $user->name ?? 'Pengguna' }}!
            </div>

            <p style="margin-top: 0; color: #475569; font-size: 14px;">
                Anda menerima notifikasi baru dari sistem terkait aktivitas akun atau sampel Anda:
            </p>

            <div class="card-alert">
                <div class="alert-title">
                    {{ $notification->title }}
                </div>
                <div class="alert-message">
                    {{ $notification->message }}
                </div>
            </div>

            @if($actionUrl)
            <div class="action-container">
                <a href="{{ $actionUrl }}" class="btn-action" target="_blank">
                    Buka Halaman Terkait &rarr;
                </a>
            </div>
            @endif

            <div class="meta-info">
                Waktu Notifikasi: <strong>{{ $notification->created_at ? $notification->created_at->format('d M Y, H:i') : now()->format('d M Y, H:i') }} WIB</strong><br>
                Jika tombol di atas tidak berfungsi, salin dan tempel tautan berikut ke browser Anda:<br>
                <a href="{{ $actionUrl }}" style="color: #059669; word-break: break-all;">{{ $actionUrl }}</a>
            </div>
        </div>

        <div class="footer">
            <p style="margin: 0 0 6px;">Email ini dikirim secara otomatis oleh Sistem Laboratorium Lingkungan.</p>
            <p style="margin: 0;">&copy; {{ date('Y') }} {{ $appName }}. Hak Cipta Dilindungi.</p>
        </div>
    </div>
</body>
</html>
