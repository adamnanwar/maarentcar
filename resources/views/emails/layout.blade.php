<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'MaaRentCar' }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7fa;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }
        .header {
            background: linear-gradient(135deg, #362EED 0%, #6366f1 100%);
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 28px;
            font-weight: 700;
        }
        .header p {
            color: rgba(255, 255, 255, 0.9);
            margin: 10px 0 0;
            font-size: 14px;
        }
        .content {
            padding: 40px 30px;
        }
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
        }
        .message {
            font-size: 15px;
            line-height: 1.6;
            color: #555;
        }
        .info-box {
            background-color: #f8fafc;
            border-radius: 12px;
            padding: 24px;
            margin: 24px 0;
            border-left: 4px solid #362EED;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            color: #6b7280;
            font-size: 14px;
        }
        .info-value {
            color: #111827;
            font-weight: 600;
            font-size: 14px;
        }
        .price-highlight {
            color: #362EED;
            font-size: 20px;
            font-weight: 700;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }
        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }
        .status-success {
            background-color: #d1fae5;
            color: #065f46;
        }
        .status-danger {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .status-info {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #362EED 0%, #6366f1 100%);
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 15px;
            margin: 20px 0;
        }
        .footer {
            background-color: #f8fafc;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }
        .footer p {
            color: #6b7280;
            font-size: 13px;
            margin: 5px 0;
        }
        .footer a {
            color: #362EED;
            text-decoration: none;
        }
        .warning-box {
            background-color: #fef3c7;
            border-radius: 8px;
            padding: 16px;
            margin: 20px 0;
            border-left: 4px solid #f59e0b;
        }
        .warning-box p {
            color: #92400e;
            margin: 0;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>MaaRentCar</h1>
            <p>Rental Mobil Terpercaya</p>
        </div>
        <div class="content">
            @yield('content')
        </div>
        <div class="footer">
            <p><strong>MaaRentCar</strong></p>
            <p>Jika ada pertanyaan, hubungi kami di <a href="mailto:support@maarentcar.com">support@maarentcar.com</a></p>
            <p>&copy; {{ date('Y') }} MaaRentCar. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
