<!DOCTYPE html>
<html dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
<meta charset="utf-8">
<style>
  body{margin:0;padding:0;background:#f4f4f5;font-family:Tahoma,'Segoe UI',Arial,sans-serif}
  .wrap{max-width:560px;margin:0 auto;padding:24px 16px}
  .card{background:#fff;border-radius:14px;overflow:hidden}
  .head{background:#1e3a5f;padding:26px;text-align:center;color:#fff;font-size:19px;font-weight:700}
  .body{padding:26px;color:#4b5563;font-size:14px;line-height:1.8}
  .footer{text-align:center;color:#9ca3af;font-size:12px;padding:18px 26px 24px}
</style>
</head>
<body>
<div class="wrap"><div class="card">
  <div class="head">{{ config('app.name') }}</div>
  <div class="body">
    <p>{{ __('messages.statement_email_greeting', ['name' => $client->name]) }}</p>
    <p>{{ __('messages.statement_email_body', ['from' => $from, 'to' => $to]) }}</p>
    <p>{{ __('messages.statement_email_attached') }}</p>
  </div>
  <div class="footer">{{ __('messages.invoice_reminder_footer') }}</div>
</div></div>
</body>
</html>
