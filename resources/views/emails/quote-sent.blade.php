<!DOCTYPE html>
<html dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <style>
        body{margin:0;padding:0;background:#f4f4f5;font-family:Tahoma,'Segoe UI',Arial,sans-serif}
        .wrap{max-width:580px;margin:0 auto;padding:24px 16px}
        .card{background:#fff;border-radius:14px;overflow:hidden}
        .head{background:#1e3a5f;padding:28px;text-align:center}
        .head h1{color:#fff;font-size:20px;margin:0 0 4px}
        .head p{color:#94b8d9;font-size:13px;margin:0}
        .body{padding:28px}
        .body h2{font-size:16px;color:#1f2937;margin:0 0 12px}
        .body p{font-size:14px;color:#4b5563;line-height:1.7;margin:0 0 10px}
        .info{background:#f8fafc;border-radius:10px;padding:16px;margin:16px 0}
        .info td{padding:6px 0;font-size:13px}
        .info .lbl{color:#6b7280}
        .info .val{color:#1f2937;font-weight:600}
        .total-box{background:#eef2ff;border-radius:10px;padding:16px;text-align:center;margin:16px 0}
        .total-box .num{font-size:22px;font-weight:700;color:#1e3a5f}
        .total-box .lbl{font-size:12px;color:#6b7280}
        .btn{display:inline-block;background:#2563eb;color:#fff;padding:12px 28px;border-radius:8px;text-decoration:none;font-size:14px;font-weight:600}
        hr{border:0;border-top:1px solid #e5e7eb;margin:20px 0}
        .footer{text-align:center;color:#9ca3af;font-size:12px;padding:20px 28px 28px}
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <div class="head">
            <h1>{{ $quote->client?->name }}</h1>
            <p>{{ __('finance.quote') }} {{ $quote->quote_number }}</p>
        </div>
        <div class="body">
            <h2>{{ __('finance.quote_sent_greeting', ['name' => $quote->client?->name]) }}</h2>
            <p>{{ __('finance.quote_sent_body', ['number' => $quote->quote_number]) }}</p>

            <table class="info" width="100%">
                <tr><td class="lbl">{{ __('finance.quote_number') }}</td><td class="val">{{ $quote->quote_number }}</td></tr>
                <tr><td class="lbl">{{ __('finance.date') }}</td><td class="val">{{ $quote->created_at?->format('Y-m-d') }}</td></tr>
                @if($quote->valid_until)<tr><td class="lbl">{{ __('finance.valid_until') }}</td><td class="val">{{ $quote->valid_until->format('Y-m-d') }}</td></tr>@endif
                <tr><td class="lbl">{{ __('finance.subject') }}</td><td class="val">{{ $quote->subject }}</td></tr>
            </table>

            <div class="total-box">
                <div class="lbl">{{ __('finance.total') }}</div>
                <div class="num">{{ number_format($quote->total, 2) }} {{ $currency ?? __('messages.currency') }}</div>
            </div>

            <div style="text-align:center">
                <a href="{{ route('quotes.public-approve-form', $quote->public_token) }}" class="btn" style="background:#059669">
                    {{ __('finance.approve_quote') }}
                </a>
                <a href="{{ route('quotes.public', $quote->public_token) }}" class="btn" style="background:#6b7280;margin-inline-start:8px">
                    {{ __('finance.view_quote') }}
                </a>
            </div>

            <hr>
            <p style="font-size:13px;color:#9ca3af">{{ __('finance.quote_sent_footer') }}</p>
        </div>
        <div class="footer">
            {{ config('app.name') }} &copy; {{ date('Y') }}
        </div>
    </div>
</div>
</body>
</html>
