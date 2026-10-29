@php
    $isRtl = app()->getLocale() === 'ar' || preg_match('/[\p{Arabic}]/u', trim((string) ($invoice->client?->name ?? '') . ' ' . (string) ($invoice->client?->company_name ?? '')));
    $companyName = $settings['company_name_ar'] ?? $settings['company_name'] ?? config('app.name');
    $currency = $settings['currency_symbol'] ?? workspace_config('currency.symbol') ?? 'ر.س';
    $logoData = pdf_logo_data($settings['company_logo'] ?? null);
@endphp
<!doctype html>
<html lang="{{ $isRtl ? 'ar' : 'en' }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <style>
        body{font-family:dejavusans,sans-serif;font-size:12px;color:#1f2937;margin:0}
        .page{padding:28px}
        .header{display:table;width:100%;margin-bottom:18px}
        .header > div{display:table-cell;vertical-align:top;width:50%}
        .box{border:1px solid #e5e7eb;border-radius:12px;padding:14px;margin-bottom:14px}
        .title{font-size:22px;font-weight:700;margin:0 0 6px}
        .sub,.muted{color:#6b7280}
        .sub{font-size:13px;margin:0}
        .section-title{font-size:13px;font-weight:700;margin:0 0 8px}
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
            <p class="title" style="margin-bottom:4px">{{ __('finance.invoice') }}</p>
            <p class="sub"><strong>{{ __('finance.invoice_number') }}:</strong> {{ $invoice->invoice_number }}</p>
            <p class="sub"><strong>{{ __('finance.invoice_date') }}:</strong> {{ $invoice->invoice_date?->format('Y-m-d') }}</p>
            @if($invoice->due_date)<p class="sub"><strong>{{ __('finance.due_date') }}:</strong> {{ $invoice->due_date?->format('Y-m-d') }}</p>@endif
        </div>
    </div>

    <div class="box">
        <p class="section-title">{{ __('finance.client_info') }}</p>
        <p style="margin:0 0 4px"><strong>{{ $invoice->client?->name }}</strong></p>
        @if($invoice->client?->company_name)<p class="muted" style="margin:0 0 4px">{{ $invoice->client->company_name }}</p>@endif
        @if($invoice->project?->name)<p class="muted" style="margin:0">{{ $invoice->project->name }}</p>@endif
    </div>

    <div class="box">
        <table>
            <thead><tr><th>{{ __('finance.description') }}</th><th style="width:80px">{{ __('finance.quantity') }}</th><th style="width:120px">{{ __('finance.unit_price') }}</th><th style="width:120px">{{ __('finance.total') }}</th></tr></thead>
            <tbody>
                @foreach($invoice->items as $item)
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
        <tr><td>{{ __('finance.subtotal') }}</td><td style="text-align:end">{{ number_format($invoice->subtotal, 2) }} {{ $currency }}</td></tr>
        <tr><td>{{ __('finance.tax') }}</td><td style="text-align:end">{{ number_format($invoice->tax_amount, 2) }} {{ $currency }}</td></tr>
        <tr><td>{{ __('finance.discount') }}{{ $invoice->discount_type === 'percentage' ? ' ('.($invoice->discount_value ?? $invoice->discount_amount).'%)' : '' }}</td><td style="text-align:end">-{{ number_format($invoice->discount_amount, 2) }} {{ $currency }}</td></tr>
        <tr><td><strong>{{ __('finance.total') }}</strong></td><td style="text-align:end"><strong>{{ number_format($invoice->total, 2) }} {{ $currency }}</strong></td></tr>
        <tr><td>{{ __('finance.paid_amount') }}</td><td style="text-align:end">{{ number_format($invoice->paid_amount, 2) }} {{ $currency }}</td></tr>
        <tr><td>{{ __('finance.remaining_amount') }}</td><td style="text-align:end">{{ number_format($invoice->remaining_amount, 2) }} {{ $currency }}</td></tr>
    </table>

    @if($invoice->notes || $invoice->terms)
    <div class="box">
        @if($invoice->notes)
            <p class="section-title">{{ __('finance.notes') }}</p>
            <p style="margin-top:0;white-space:pre-line">{{ $invoice->notes }}</p>
        @endif
        @if($invoice->terms)
            <p class="section-title">{{ __('finance.terms_and_conditions') }}</p>
            <p style="margin-top:0;white-space:pre-line">{{ $invoice->terms }}</p>
        @endif
    </div>
    @endif
</div>
</body>
</html>
