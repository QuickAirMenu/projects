<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ is_rtl() ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('finance.confirm_approve_title') }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{background:#f4f4f5;font-family:Tahoma,'Segoe UI',Arial,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:24px}
        .card{background:#fff;border-radius:16px;box-shadow:0 4px 24px rgba(0,0,0,.08);padding:40px;max-width:560px;width:100%;text-align:center}
        .icon{font-size:56px;margin-bottom:16px;color:#059669}
        h1{font-size:20px;color:#1f2937;margin-bottom:8px}
        p{font-size:14px;color:#6b7280;line-height:1.7;margin-bottom:24px}
        .info{background:#f8fafc;border-radius:10px;padding:16px;margin-bottom:24px;text-align:start}
        .info div{padding:4px 0;font-size:13px;color:#374151}
        .info .lbl{color:#6b7280}
        .btn{display:inline-block;padding:12px 32px;border-radius:8px;font-size:14px;font-weight:600;text-decoration:none;border:none;cursor:pointer;transition:background .15s}
        .btn-primary{background:#059669;color:#fff}
        .btn-primary:hover{background:#047857}
        .btn-secondary{background:#e5e7eb;color:#374151;margin-inline-start:8px}
        .btn-secondary:hover{background:#d1d5db}
    </style>
</head>
<body>
<div class="card">
    <div class="icon"><i class="fas {{ $quote->status === 'approved' ? 'fa-check-circle text-green-600' : 'fa-file-signature' }}"></i></div>

    @if($quote->status === 'approved')
        <h1 style="color:#065f46">✅ تم اعتماد هذا العرض مسبقاً</h1>
        <p>تم اعتماد عرض السعر رقم {{ $quote->quote_number }} في {{ $quote->approved_at?->format('Y-m-d H:i') }}</p>
    @elseif($quote->status === 'rejected')
        <h1 style="color:#991b1b">تم طلب تعديل على هذا العرض</h1>
        @if($quote->rejection_reason)<p>{{ $quote->rejection_reason }}</p>@endif
    @else
        <h1>{{ __('finance.confirm_approve_title') }}</h1>
        <p>{{ __('finance.approve_confirm_text') }}</p>
    @endif

    <div class="info">
        <div><span class="lbl">{{ __('finance.quote_number') }}:</span> {{ $quote->quote_number }}</div>
        <div><span class="lbl">{{ __('client.client') }}:</span> {{ $quote->client?->name }}</div>
        <div><span class="lbl">{{ __('finance.total') }}:</span> {{ number_format($quote->total, 2) }} {{ __('messages.currency') }}</div>
        @if($quote->valid_until)<div><span class="lbl">{{ __('finance.valid_until') }}:</span> {{ $quote->valid_until->format('Y-m-d') }}</div>@endif
    </div>

    @if($quote->notes || $quote->terms_and_conditions)
    <div class="info" style="background:#fffbeb;border:1px solid #fde68a">
        @if($quote->notes)
        <div style="padding:4px 0;font-size:13px;color:#374151"><span class="lbl">{{ __('messages.notes') }}:</span></div>
        <div style="padding:4px 0 8px;font-size:12px;color:#6b7280;white-space:pre-line">{{ $quote->notes }}</div>
        @endif
        @if($quote->terms_and_conditions)
        <div style="padding:4px 0;font-size:13px;color:#374151"><span class="lbl">{{ __('finance.terms_and_conditions') }}:</span></div>
        <div style="padding:4px 0 0;font-size:12px;color:#6b7280;white-space:pre-line">{{ $quote->terms_and_conditions }}</div>
        @endif
    </div>
    @endif

    @if($quote->status === 'sent')
    <form action="{{ route('quotes.public-approve', $quote->public_token) }}" method="POST" style="display:inline">
        @csrf
        <button type="submit" class="btn btn-primary"><i class="fas fa-check-circle" style="margin-inline-end:6px"></i>{{ __('finance.approve_quote_confirm') }}</button>
        <a href="{{ route('quotes.public', $quote->public_token) }}" class="btn btn-secondary">{{ __('messages.cancel') }}</a>
    </form>
    @else
    <a href="{{ route('quotes.public', $quote->public_token) }}" class="btn btn-secondary">{{ __('finance.view_quote') }}</a>
    @endif
</div>
</body>
</html>
