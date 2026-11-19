@php
    $isRtl = app()->getLocale() === 'ar';
    $companyName = $settings['company_name_ar'] ?? $settings['company_name'] ?? config('app.name');
    $currency = $account->currency;
    $logoData = pdf_logo_data($settings['company_logo'] ?? null);
@endphp
<!doctype html>
<html lang="{{ $isRtl ? 'ar' : 'en' }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <style>
        body{font-family:dejavusans,sans-serif;font-size:11px;color:#1f2937;margin:0}
        .page{padding:26px}
        .header{display:table;width:100%;margin-bottom:14px}
        .header > div{display:table-cell;vertical-align:top;width:50%}
        .title{font-size:20px;font-weight:700;margin:0 0 4px}
        .muted{color:#6b7280}
        .box{border:1px solid #e5e7eb;border-radius:10px;padding:12px;margin-bottom:12px}
        .meta td{padding:3px 6px;font-size:11px}
        table.ledger{width:100%;border-collapse:collapse}
        table.ledger th,table.ledger td{border-bottom:1px solid #e5e7eb;padding:7px 6px;text-align:start}
        table.ledger th{background:#f9fafb;font-size:10px}
        .num{text-align:end}
        tr.opening td, tr.closing td{background:#f3f4f6;font-weight:700}
        .totals{width:45%;margin-inline-start:auto;margin-top:10px}
        .totals td{border:0;padding:5px 6px}
    </style>
</head>
<body>
<div class="page">
    <div class="header">
        <div>
            @if($logoData)<img src="{{ $logoData }}" style="max-height:52px;margin-bottom:8px">@endif
            <p class="title">{{ $companyName }}</p>
            <p class="muted" style="margin:0">{{ $settings['company_email'] ?? '' }} {{ $settings['company_phone'] ?? '' }}</p>
        </div>
        <div style="text-align:{{ $isRtl ? 'left' : 'right' }}">
            <p class="title">{{ __('accounts.statement') }}</p>
            <p class="muted" style="margin:0">{{ __('accounts.from') }}: {{ $from }} — {{ __('accounts.to') }}: {{ $to }}</p>
        </div>
    </div>

    <div class="box">
        <table class="meta" width="100%">
            <tr><td><strong>{{ __('accounts.name') }}:</strong> {{ $account->name }}</td><td><strong>{{ __('accounts.currency') }}:</strong> {{ $currency }}</td></tr>
            <tr><td><strong>{{ __('accounts.type') }}:</strong> {{ __('accounts.type_'.$account->type) }}</td><td><strong>IBAN:</strong> {{ $account->iban ?: '-' }}</td></tr>
        </table>
    </div>

    <table class="ledger">
        <thead>
            <tr>
                <th style="width:70px">{{ __('messages.date') }}</th>
                <th>{{ __('accounts.description') }}</th>
                <th style="width:85px">{{ __('accounts.category') }}</th>
                <th class="num" style="width:70px">{{ __('accounts.in') }}</th>
                <th class="num" style="width:70px">{{ __('accounts.out') }}</th>
                <th class="num" style="width:80px">{{ __('accounts.running_balance') }}</th>
            </tr>
        </thead>
        <tbody>
            <tr class="opening">
                <td>{{ $from }}</td>
                <td colspan="4">{{ __('accounts.opening_balance') }}</td>
                <td class="num">{{ number_format($opening, 2) }}</td>
            </tr>
            @foreach($rows as $row)
            @php $tx = $row['tx']; @endphp
            <tr>
                <td>{{ $tx->transaction_date?->format('Y-m-d') }}</td>
                <td>{{ $tx->description ?: '-' }}</td>
                <td>{{ __('accounts.cat_'.$tx->category) }}</td>
                <td class="num">{{ $tx->direction === 'in' ? number_format((float) $tx->amount, 2) : '' }}</td>
                <td class="num">{{ $tx->direction === 'out' ? number_format((float) $tx->amount, 2) : '' }}</td>
                <td class="num">{{ number_format($row['balance'], 2) }}</td>
            </tr>
            @endforeach
            <tr class="closing">
                <td>{{ $to }}</td>
                <td colspan="4">{{ __('accounts.closing_balance') }}</td>
                <td class="num">{{ number_format($closing, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <table class="totals">
        <tr><td>{{ __('accounts.total_in') }}</td><td class="num">{{ number_format($totalIn, 2) }} {{ $currency }}</td></tr>
        <tr><td>{{ __('accounts.total_out') }}</td><td class="num">{{ number_format($totalOut, 2) }} {{ $currency }}</td></tr>
        <tr><td><strong>{{ __('accounts.net_change') }}</strong></td><td class="num"><strong>{{ number_format($totalIn - $totalOut, 2) }} {{ $currency }}</strong></td></tr>
    </table>

    <p class="muted" style="margin-top:18px;font-size:9px">{{ __('accounts.statement_footer') }} — {{ now()->format('Y-m-d H:i') }}</p>
</div>
</body>
</html>
