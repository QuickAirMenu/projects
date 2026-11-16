<!DOCTYPE html>
<html dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <style>
        body{margin:0;padding:0;background:#f4f4f5;font-family:Tahoma,'Segoe UI',Arial,sans-serif}
        .wrap{max-width:560px;margin:0 auto;padding:24px 16px}
        .card{background:#fff;border-radius:14px;overflow:hidden}
        .head{background:#1e3a5f;padding:26px;text-align:center}
        .head h1{color:#fff;font-size:19px;margin:0}
        .body{padding:26px}
        .body p{font-size:14px;color:#4b5563;line-height:1.8;margin:0 0 12px}
        .info{background:#f8fafc;border-radius:10px;padding:16px;margin:16px 0;width:100%}
        .info td{padding:6px 0;font-size:13px}
        .info .lbl{color:#6b7280}
        .info .val{color:#1f2937;font-weight:600}
        .due{background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;border-radius:10px;padding:12px 16px;font-size:14px;font-weight:700;text-align:center;margin:14px 0}
        .btn{display:inline-block;background:#2563eb;color:#fff;padding:12px 26px;border-radius:8px;text-decoration:none;font-size:14px;font-weight:600}
        .footer{text-align:center;color:#9ca3af;font-size:12px;padding:18px 26px 24px}
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <div class="head"><h1>{{ $companyName }}</h1></div>
        <div class="body">
            <p>{{ __('messages.invoice_reminder_greeting', ['name' => $invoice->client?->name ?? '']) }}</p>
            <p>{{ __('messages.invoice_reminder_body') }}</p>

            <table class="info" cellpadding="0" cellspacing="0">
                <tr><td class="lbl">{{ __('finance.invoice_number') }}</td><td class="val">{{ $invoice->invoice_number }}</td></tr>
                <tr><td class="lbl">{{ __('finance.invoice_date') }}</td><td class="val">{{ $invoice->invoice_date?->format('Y-m-d') }}</td></tr>
                <tr><td class="lbl">{{ __('finance.due_date') }}</td><td class="val">{{ $invoice->due_date?->format('Y-m-d') }}</td></tr>
                <tr><td class="lbl">{{ __('finance.total') }}</td><td class="val">{{ number_format((float) $invoice->total, 2) }} {{ $currency }}</td></tr>
                <tr><td class="lbl">{{ __('finance.remaining') }}</td><td class="val">{{ number_format((float) $invoice->remaining_amount, 2) }} {{ $currency }}</td></tr>
            </table>

            <div class="due">{{ __('messages.invoice_reminder_amount_due', ['amount' => number_format((float) $invoice->remaining_amount, 2), 'currency' => $currency]) }}</div>

            <p style="text-align:center;margin-top:18px">
                <a href="{{ route('portal.login') }}" class="btn">{{ __('messages.invoice_reminder_pay_link') }}</a>
            </p>
        </div>
        <div class="footer">{{ __('messages.invoice_reminder_footer') }}</div>
    </div>
</div>
</body>
</html>
