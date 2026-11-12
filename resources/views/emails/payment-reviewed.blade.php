<!DOCTYPE html>
<html dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <style>
        body{margin:0;padding:0;background:#f4f4f5;font-family:Tahoma,'Segoe UI',Arial,sans-serif}
        .wrap{max-width:580px;margin:0 auto;padding:24px 16px}
        .card{background:#fff;border-radius:14px;overflow:hidden}
        .head{background:{{ $approved ? '#065f46' : '#991b1b' }};padding:28px;text-align:center}
        .head h1{color:#fff;font-size:20px;margin:0 0 4px}
        .head p{color:rgba(255,255,255,.75);font-size:13px;margin:0}
        .body{padding:28px}
        .body h2{font-size:16px;color:#1f2937;margin:0 0 12px}
        .body p{font-size:14px;color:#4b5563;line-height:1.7;margin:0 0 10px}
        .info{background:#f8fafc;border-radius:10px;padding:16px;margin:16px 0;width:100%}
        .info td{padding:6px 0;font-size:13px}
        .info .lbl{color:#6b7280}
        .info .val{color:#1f2937;font-weight:600}
        .btn{display:inline-block;background:#2563eb;color:#fff;padding:12px 28px;border-radius:8px;text-decoration:none;font-size:14px;font-weight:600}
        .reason{background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;border-radius:10px;padding:12px 16px;font-size:13px;margin:14px 0}
        .footer{text-align:center;color:#9ca3af;font-size:12px;padding:20px 28px 28px}
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <div class="head">
            <h1>{{ $companyName }}</h1>
            <p>{{ $approved ? __('portal.payment_approved_title') : __('portal.payment_rejected_title') }}</p>
        </div>
        <div class="body">
            <h2>{{ __('portal.payment_review_email_heading') }}</h2>
            <p>
                {{ $approved
                    ? __('portal.payment_approved_msg', ['amount' => number_format((float) $payment->amount, 2), 'invoice' => $payment->invoice?->invoice_number])
                    : __('portal.payment_rejected_msg', ['amount' => number_format((float) $payment->amount, 2), 'invoice' => $payment->invoice?->invoice_number, 'reason' => $payment->rejection_reason ?: '-']) }}
            </p>

            <table class="info" cellpadding="0" cellspacing="0">
                <tr><td class="lbl">{{ __('finance.payment_number') }}</td><td class="val">{{ $payment->payment_number }}</td></tr>
                <tr><td class="lbl">{{ __('finance.invoice') }}</td><td class="val">{{ $payment->invoice?->invoice_number }}</td></tr>
                <tr><td class="lbl">{{ __('finance.amount') }}</td><td class="val">{{ number_format((float) $payment->amount, 2) }} {{ $currency }}</td></tr>
                <tr><td class="lbl">{{ __('finance.date') }}</td><td class="val">{{ $payment->payment_date?->format('Y-m-d') }}</td></tr>
            </table>

            @if(! $approved && $payment->rejection_reason)
                <div class="reason">{{ __('portal.rejection_reason') }}: {{ $payment->rejection_reason }}</div>
            @endif

            @if($payment->invoice)
                <p style="text-align:center;margin-top:20px">
                    <a href="{{ route('portal.invoices.show', $payment->invoice->id) }}" class="btn">{{ __('portal.view_invoice') }}</a>
                </p>
            @endif
        </div>
        <div class="footer">{{ $companyName }} — {{ __('portal.payment_review_email_footer') }}</div>
    </div>
</div>
</body>
</html>
