<!DOCTYPE html>
<html dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head><meta charset="utf-8"><style>
body{margin:0;padding:0;background:#f4f4f5;font-family:Tahoma,'Segoe UI',Arial,sans-serif}
.wrap{max-width:560px;margin:0 auto;padding:24px 16px}
.card{background:#fff;border-radius:14px;overflow:hidden}
.head{background:#1e3a5f;padding:26px;text-align:center;color:#fff;font-size:19px;font-weight:700}
.body{padding:26px;color:#4b5563;font-size:14px;line-height:1.8}
.info{background:#f8fafc;border-radius:10px;padding:16px;margin:16px 0;width:100%}
.info td{padding:6px 0;font-size:13px}.lbl{color:#6b7280}.val{color:#1f2937;font-weight:600}
.total{background:#eef2ff;border-radius:10px;padding:14px;text-align:center;font-weight:700;color:#1e3a5f}
.footer{text-align:center;color:#9ca3af;font-size:12px;padding:18px 26px 24px}
</style></head>
<body>
<div class="wrap"><div class="card">
  <div class="head">{{ $companyName }}</div>
  <div class="body">
    <p>{{ __('messages.invoice_issued_greeting', ['name' => $invoice->client?->name ?? '']) }}</p>
    <p>{{ __('messages.invoice_issued_body') }}</p>
    <table class="info" cellpadding="0" cellspacing="0">
      <tr><td class="lbl">{{ __('finance.invoice_number') }}</td><td class="val">{{ $invoice->invoice_number }}</td></tr>
      <tr><td class="lbl">{{ __('finance.invoice_date') }}</td><td class="val">{{ $invoice->invoice_date?->format('Y-m-d') }}</td></tr>
      <tr><td class="lbl">{{ __('finance.due_date') }}</td><td class="val">{{ $invoice->due_date?->format('Y-m-d') }}</td></tr>
    </table>
    <div class="total">{{ __('messages.invoice_issued_total', ['amount' => number_format((float) $invoice->total, 2), 'currency' => $currency]) }}</div>
    <p>{{ __('messages.invoice_issued_attached') }}</p>
  </div>
  <div class="footer">{{ __('messages.invoice_reminder_footer') }}</div>
</div></div>
</body>
</html>
