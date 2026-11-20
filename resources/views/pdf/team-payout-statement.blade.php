@php
    $isRtl = app()->getLocale() === 'ar';
    $companyName = $settings['company_name_ar'] ?? $settings['company_name'] ?? config('app.name');
    $logoData = pdf_logo_data($settings['company_logo'] ?? null);
    $baseCurrency = $baseCurrency ?? 'SAR';
@endphp
<!doctype html>
<html lang="{{ $isRtl ? 'ar' : 'en' }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head><meta charset="utf-8"><style>
body{font-family:dejavusans,sans-serif;font-size:11px;color:#1f2937;margin:0}
.page{padding:26px}.title{font-size:20px;font-weight:700;margin:0 0 4px}.muted{color:#6b7280}
.header{display:table;width:100%;margin-bottom:14px}.header>div{display:table-cell;vertical-align:top;width:50%}
.box{border:1px solid #e5e7eb;border-radius:10px;padding:12px;margin-bottom:12px}
table{width:100%;border-collapse:collapse}th,td{border-bottom:1px solid #e5e7eb;padding:7px 6px;text-align:start}
th{background:#f9fafb;font-size:10px}.num{text-align:end}
.totals{width:45%;margin-inline-start:auto;margin-top:10px}.totals td{border:0;padding:5px 6px}
</style></head>
<body><div class="page">
<div class="header">
    <div>@if($logoData)<img src="{{ $logoData }}" style="max-height:52px;margin-bottom:8px">@endif
        <p class="title">{{ $companyName }}</p>
        <p class="muted" style="margin:0">{{ $settings['company_email'] ?? '' }} {{ $settings['company_phone'] ?? '' }}</p></div>
    <div style="text-align:{{ $isRtl ? 'left' : 'right' }}">
        <p class="title">{{ __('reports.payout_statement') }}</p>
        <p class="muted" style="margin:0">{{ $from }} — {{ $to }}</p></div>
</div>

<div class="box"><table>
<tr><td><strong>{{ __('reports.team_member') }}:</strong> {{ $member->name }}</td><td><strong>{{ __('reports.team_tasks') }}:</strong> {{ $tasks->count() }}</td></tr>
</table></div>

<table>
<thead><tr><th>{{ __('messages.date') }}</th><th>{{ __('time.task') }}</th><th>{{ __('common.project') }}</th><th class="num">{{ __('task.team_cost') }}</th><th class="num">{{ __('reports.base_value') }}</th><th>{{ __('accounts.title') }}</th></tr></thead>
<tbody>
@foreach($tasks as $task)
<tr>
    <td>{{ $task->cost_paid_at?->format('Y-m-d') }}</td>
    <td>{{ $task->title }}</td>
    <td>{{ $task->project?->name ?? '-' }}</td>
    <td class="num">{{ number_format((float) $task->cost_amount, 2) }} {{ $task->cost_currency ?: $baseCurrency }}</td>
    <td class="num">{{ number_format((float) $task->cost_amount * (float) ($task->cost_exchange_rate ?: 1), 2) }}</td>
    <td>{{ $task->paidAccount?->name ?? '-' }}</td>
</tr>
@endforeach
</tbody></table>

<table class="totals">
<tr><td>{{ __('reports.base_total') }}</td><td class="num">{{ number_format($baseTotal, 2) }} {{ $baseCurrency }}</td></tr>
@foreach($byCurrency as $currency => $total)
<tr><td>{{ $currency }}</td><td class="num">{{ number_format($total, 2) }}</td></tr>
@endforeach
</table>
<p class="muted" style="margin-top:18px;font-size:9px">{{ __('accounts.statement_footer') }} — {{ now()->format('Y-m-d H:i') }}</p>
</div></body></html>
