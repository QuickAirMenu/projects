@php
    $isRtl = app()->getLocale() === 'ar' || preg_match('/[\p{Arabic}]/u', trim((string) ($contract->client?->name ?? '') . ' ' . (string) ($contract->client?->company_name ?? '')));
    $companyName = $settings['company_name_ar'] ?? $settings['company_name'] ?? config('app.name');
    $currency = $settings['currency_symbol'] ?? workspace_config('currency.symbol') ?? 'ر.س';
    $logoData = pdf_logo_data($settings['company_logo'] ?? null);
@endphp
<!doctype html>
<html lang="{{ $isRtl ? 'ar' : 'en' }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <style>
        body{font-family:DejaVu Sans,sans-serif;font-size:12px;color:#1f2937;margin:0}
        .page{padding:28px}
        .header{display:table;width:100%;margin-bottom:18px}
        .header > div{display:table-cell;vertical-align:top;width:50%}
        .box{border:1px solid #e5e7eb;border-radius:12px;padding:14px;margin-bottom:14px}
        .title{font-size:22px;font-weight:700;margin:0 0 6px}
        .sub,.muted{color:#6b7280}
        .sub{font-size:13px;margin:0}
        .section-title{font-size:13px;font-weight:700;margin:0 0 8px}
    </style>
</head>
<body>
<div class="page">
    <div class="header">
        <div>
            @if($logoData)<img src="{{ $logoData }}" style="max-height:56px;margin-bottom:10px">@endif
            <p class="title">{{ $companyName }}</p>
            <p class="sub">{{ $settings['company_email'] ?? '-' }} | {{ $settings['company_phone'] ?? '-' }}</p>
            <p class="sub">{{ $settings['company_address'] ?? '-' }}</p>
        </div>
        <div style="text-align:{{ $isRtl ? 'left' : 'right' }}">
            <p class="title" style="margin-bottom:4px">عقد</p>
            <p class="sub"><strong>الرقم:</strong> {{ $contract->contract_number }}</p>
            <p class="sub"><strong>التاريخ:</strong> {{ $contract->created_at?->format('Y-m-d') }}</p>
        </div>
    </div>

    <div class="box">
        <p class="section-title">معلومات العميل</p>
        <p style="margin:0 0 4px"><strong>{{ $contract->client?->name }}</strong></p>
        @if($contract->client?->company_name)<p class="muted" style="margin:0">{{ $contract->client->company_name }}</p>@endif
    </div>

    <div class="box">
        <p class="section-title">{{ $contract->title }}</p>
        @if($contract->description)<p style="margin-top:0;white-space:pre-line">{{ $contract->description }}</p>@endif
        <p><strong>قيمة العقد:</strong> {{ number_format($contract->contract_value, 2) }} {{ $currency }}</p>
        <p><strong>تاريخ البداية:</strong> {{ $contract->start_date?->format('Y-m-d') }}</p>
        @if($contract->end_date)<p><strong>تاريخ الانتهاء:</strong> {{ $contract->end_date?->format('Y-m-d') }}</p>@endif
        @if($contract->deliverables)
            <p class="section-title" style="margin-top:12px">المخرجات</p>
            <p style="margin-top:0;white-space:pre-line">{{ $contract->deliverables }}</p>
        @endif
        @if($contract->payment_terms)
            <p class="section-title" style="margin-top:12px">شروط الدفع</p>
            <p style="margin-top:0;white-space:pre-line">{{ $contract->payment_terms }}</p>
        @endif
        @if($contract->terms_and_conditions)
            <p class="section-title" style="margin-top:12px">الشروط والأحكام</p>
            <p style="margin-top:0;white-space:pre-line">{{ $contract->terms_and_conditions }}</p>
        @endif
        <p><strong>الحالة:</strong> {{ ucfirst($contract->status) }}</p>
        <p><strong>موقّع من العميل:</strong> {{ $contract->signed_by_client ? 'نعم' : 'لا' }}</p>
        @if($contract->signed_at)<p><strong>تاريخ التوقيع:</strong> {{ $contract->signed_at?->format('Y-m-d') }}</p>@endif
    </div>
</div>
</body>
</html>
