@php
    $isRtl = app()->getLocale() === 'ar' || preg_match('/[\p{Arabic}]/u', trim((string) ($quote->client?->name ?? '') . ' ' . (string) ($quote->client?->company_name ?? '')));
    $companyName = $settings['company_name_ar'] ?? $settings['company_name'] ?? config('app.name');
    $currency = $settings['currency_symbol'] ?? workspace_config('currency.symbol') ?? 'ر.س';
    $logoData = pdf_logo_data($settings['company_logo'] ?? null);
@endphp
<!doctype html>
<html lang="{{ $isRtl ? 'ar' : 'en' }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <style>
        body{font-family:dejavusans,sans-serif;font-size:11px;color:#1f2937;margin:0}
        .page{padding:20px}
        .header{display:table;width:100%;margin-bottom:18px}
        .header > div{display:table-cell;vertical-align:top;width:50%}
        .box{border:1px solid #e5e7eb;border-radius:10px;padding:10px;margin-bottom:10px}
        .title{font-size:22px;font-weight:700;margin:0 0 6px}
        .sub,.muted{color:#6b7280}
        .sub{font-size:13px;margin:0}
        .section-title{font-size:12px;font-weight:700;margin:0 0 5px}
        table{width:100%;border-collapse:collapse}
        th,td{border-bottom:1px solid #e5e7eb;padding:10px 8px;text-align:start}
        th{background:#f9fafb;font-size:11px}
        .totals{width:38%;margin-left:auto;margin-top:8px}
        .totals td{border-bottom:0;padding:6px 8px}
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
            <p class="title" style="margin-bottom:4px">{{ __('finance.quote') }}</p>
            <p class="sub"><strong>{{ __('finance.quote_number') }}:</strong> {{ $quote->quote_number }}</p>
            <p class="sub"><strong>{{ __('finance.date') }}:</strong> {{ $quote->created_at?->format('Y-m-d') }}</p>
            @if($quote->valid_until)<p class="sub"><strong>{{ __('finance.valid_until') }}:</strong> {{ $quote->valid_until?->format('Y-m-d') }}</p>@endif
        </div>
    </div>

    <div class="box">
        <p class="section-title">{{ __('finance.client_info') }}</p>
        <p style="margin:0 0 4px"><strong>{{ $quote->client?->name }}</strong></p>
        @if($quote->client?->company_name)<p class="muted" style="margin:0 0 4px">{{ $quote->client->company_name }}</p>@endif
        @if($quote->client?->email)<p class="muted" style="margin:0">{{ $quote->client->email }}</p>@endif
    </div>

    <div class="box">
        <p class="section-title">{{ $quote->subject }}</p>
        @if($quote->description)<p style="margin-top:0;white-space:pre-line">{{ $quote->description }}</p>@endif
        <table>
            <thead><tr><th>{{ __('finance.description') }}</th><th style="width:80px">{{ __('finance.quantity') }}</th><th style="width:120px">{{ __('finance.unit_price') }}</th><th style="width:120px">{{ __('finance.total') }}</th></tr></thead>
            <tbody>
                @foreach($quote->items as $item)
                    <tr>
                        <td>{{ $item->description }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->unit_price, 2) }} {{ $currency }}</td>
                        <td>{{ number_format($item->total, 2) }} {{ $currency }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <table class="totals">
        <tr><td>{{ __('finance.subtotal') }}</td><td style="text-align:end">{{ number_format($quote->subtotal, 2) }} {{ $currency }}</td></tr>
        <tr><td>{{ __('finance.tax') }}</td><td style="text-align:end">{{ number_format($quote->tax_amount, 2) }} {{ $currency }}</td></tr>
        <tr><td>{{ __('finance.discount') }}{{ $quote->discount_type === 'percentage' ? ' ('.($quote->discount_value ?? $quote->discount_percentage).'%)' : '' }}</td><td style="text-align:end">-{{ number_format($quote->discount_amount, 2) }} {{ $currency }}</td></tr>
        <tr><td><strong>{{ __('finance.total') }}</strong></td><td style="text-align:end"><strong>{{ number_format($quote->total, 2) }} {{ $currency }}</strong></td></tr>
    </table>

    @if($quote->notes || $quote->terms_and_conditions)
    <div class="box">
        @if($quote->notes)
            <p class="section-title">{{ __('finance.notes') }}</p>
            @foreach(text_lines($quote->notes) as $line)
                <p style="margin:0 0 2px 0;font-size:10px;line-height:1.55">{{ $line }}</p>
            @endforeach
        @endif
        @if($quote->terms_and_conditions)
            <p class="section-title">{{ __('finance.terms_and_conditions') }}</p>
            @foreach(text_lines($quote->terms_and_conditions) as $line)
                <p style="margin:0 0 2px 0;font-size:10px;line-height:1.55">{{ $line }}</p>
            @endforeach
        @endif
    </div>
    @endif
</div>
</body>
</html>
