<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $quote->quote_number }}</title>
    <style>
        body{font-family:Arial, sans-serif;background:#f8fafc;color:#0f172a;margin:0;padding:24px}
        .card{max-width:900px;margin:0 auto;background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:24px}
        table{width:100%;border-collapse:collapse}
        th,td{padding:10px;border-bottom:1px solid #e2e8f0;text-align:right;font-size:14px}
        .muted{color:#64748b}
        .btn{display:inline-block;background:#2563eb;color:#fff;padding:10px 16px;border-radius:10px;text-decoration:none}
    </style>
</head>
<body>
<div class="card">
    <h1>{{ config('app.name') }}</h1>
    <p class="muted">عرض سعر رقم {{ $quote->quote_number }}</p>
    <p><strong>العميل:</strong> {{ $quote->client?->name }}</p>
    <p><strong>الحالة:</strong> {{ ucfirst($quote->status) }}</p>
    <p><strong>الرابط العام:</strong> {{ route('quotes.public', $quote->public_token) }}</p>
    <table>
        <thead>
            <tr><th>الوصف</th><th>الكمية</th><th>السعر</th><th>الإجمالي</th></tr>
        </thead>
        <tbody>
            @foreach($quote->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($item->unit_price, 2) }}</td>
                    <td>{{ number_format($item->total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <p><strong>الإجمالي:</strong> {{ number_format($quote->total, 2) }}</p>
</div>
</body>
</html>
