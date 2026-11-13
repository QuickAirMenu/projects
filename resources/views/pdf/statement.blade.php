@php
    $isRtl = app()->getLocale() === 'ar';
    $companyName = $settings['company_name_ar'] ?? $settings['company_name'] ?? config('app.name');
    $logoData = pdf_logo_data($settings['company_logo'] ?? null);
    $currency = $settings['currency_symbol'] ?? workspace_config('currency.symbol') ?? 'ر.س';
@endphp
<!doctype html>
<html lang="{{ $isRtl ? 'ar' : 'en' }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <style>
        body{font-family:dejavusans,sans-serif;font-size:11px;color:#1f2937;margin:0}
        .page{padding:20px}
        .header{display:table;width:100%;margin-bottom:14px}
        .header > div{display:table-cell;vertical-align:top;width:50%}
        .title{font-size:20px;font-weight:700;margin:0 0 4px}
        .sub{font-size:11px;color:#6b7280;margin:0}
        table{width:100%;border-collapse:collapse}
        th,td{border-bottom:1px solid #e5e7eb;padding:6px 8px;text-align:start}
        th{background:#f9fafb;font-size:10px}
        .num{text-align:end}
        .box{border:1px solid #e5e7eb;border-radius:8px;padding:10px;margin-bottom:12px}
        .kpi{display:table;width:100%}
        .kpi > div{display:table-cell;width:33%;padding:4px 6px}
        .kpi .lbl{font-size:9px;color:#6b7280}
        .kpi .val{font-size:13px;font-weight:700}
    </style>
</head>
<body>
<div class="page">
    <div class="header">
        <div>
            @if($logoData)<img src="{{ $logoData }}" style="max-height:44px;margin-bottom:8px">@endif
            <p class="title">{{ $companyName }}</p>
            <p class="sub">{{ $settings['company_phone'] ?? '' }} | {{ $settings['company_email'] ?? '' }}</p>
        </div>
        <div style="text-align:{{ $isRtl ? 'left' : 'right' }}">
            <p class="title" style="font-size:16px">{{ __('portal.statement') }}</p>
            <p class="sub">{{ __('portal.from') }}: {{ $from }} — {{ __('portal.to') }}: {{ $to }}</p>
            <p class="sub">{{ __('portal.account') }}: {{ $client->name }}</p>
        </div>
    </div>

    <div class="box">
        <div class="kpi">
            <div>
                <p class="lbl">{{ __('portal.total_invoiced') }}</p>
                <p class="val">{{ number_format($totals['invoiced'], 2) }} {{ $currency }}</p>
            </div>
            <div>
                <p class="lbl">{{ __('portal.total_paid') }}</p>
                <p class="val">{{ number_format($totals['paid'], 2) }} {{ $currency }}</p>
            </div>
            <div>
                <p class="lbl">{{ __('portal.balance_due') }}</p>
                <p class="val">{{ number_format($totals['balance'], 2) }} {{ $currency }}</p>
            </div>
        </div>
    </div>

    <table>
        <thead>
        <tr>
            <th>{{ __('portal.date') }}</th>
            <th>{{ __('portal.document') }}</th>
            <th class="num">{{ __('portal.debit') }}</th>
            <th class="num">{{ __('portal.credit') }}</th>
            <th class="num">{{ __('portal.running_balance') }}</th>
        </tr>
        </thead>
        <tbody>
        @forelse($rows as $row)
            <tr>
                <td>{{ $row['date'] }}</td>
                <td>{{ $row['label'] }}</td>
                <td class="num">{{ $row['debit'] ? number_format($row['debit'], 2) : '-' }}</td>
                <td class="num">{{ $row['credit'] ? number_format($row['credit'], 2) : '-' }}</td>
                <td class="num"><strong>{{ number_format($row['balance'], 2) }}</strong></td>
            </tr>
        @empty
            <tr><td colspan="5" style="text-align:center;color:#9ca3af">{{ __('messages.no_data') }}</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
</body>
</html>
