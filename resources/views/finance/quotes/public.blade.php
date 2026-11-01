<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ is_rtl() ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=3">
    <title>{{ $quote->quote_number }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{background:#e5e7eb;font-family:Tahoma,'Segoe UI',Arial,sans-serif;display:flex;justify-content:center;padding:0;min-height:100dvh}
        #page-wrap{width:100%;display:flex;justify-content:center;padding:0}
        .page{width:210mm;min-height:297mm;background:#fff;box-shadow:0 4px 24px rgba(0,0,0,.1);padding:22mm 25mm;position:relative;transform-origin:top center;margin:0 auto}
        .header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:28px}
        .header .brand{max-width:55%}
        .header .brand img{max-height:60px;margin-bottom:10px}
        .header .brand h1{font-size:20px;color:#1f2937}
        .header .brand p{font-size:12px;color:#6b7280;margin-top:4px}
        .header .title-box{text-align:left}
        .header .title-box h2{font-size:22px;color:#1e3a5f;margin-bottom:4px}
        .header .title-box p{font-size:12px;color:#6b7280}
        .hr{border:0;border-top:2px solid #1e3a5f;margin:20px 0}
        .info-box{border:1px solid #e5e7eb;border-radius:10px;padding:14px 18px;margin-bottom:20px}
        .info-box .label{font-size:12px;color:#6b7280;margin-bottom:4px}
        .info-box .value{font-size:14px;color:#1f2937;font-weight:600}
        .table-wrap{overflow-x:auto;-webkit-overflow-scrolling:touch;margin:20px 0}
        table{width:100%;border-collapse:collapse}
        th{background:#f8fafc;font-size:12px;color:#6b7280;padding:10px 8px;border-bottom:2px solid #e5e7eb;font-weight:600}
        td{padding:10px 8px;border-bottom:1px solid #f1f1f1;font-size:13px;color:#374151}
        .totals{width:50%;margin-left:auto;margin-top:12px}
        .totals td{padding:6px 8px;border:0;font-size:13px}
        .totals .final td{padding-top:10px;border-top:2px solid #1e3a5f;font-size:15px;font-weight:700;color:#1e3a5f}
        .notes{margin-top:24px;padding-top:16px;border-top:1px solid #e5e7eb}
        .notes h3{font-size:13px;color:#374151;margin-bottom:6px}
        .notes p{font-size:12px;color:#6b7280;white-space:pre-line;line-height:1.6}
        .actions{text-align:center;margin-top:24px;padding-top:16px;border-top:1px solid #e5e7eb}
        .btn{display:inline-block;background:#2563eb;color:#fff;padding:10px 24px;border-radius:8px;text-decoration:none;font-size:14px;font-weight:600}
        .btn:hover{background:#1d4ed8}
        @media print{
            body{background:#fff}
            #page-wrap{padding:0}
            .page{box-shadow:none;padding:22mm 25mm;min-height:297mm}
            .table-wrap{overflow:visible}
            .zoom-hint{display:none!important}
        }
    </style>
</head>
<body>
<div id="page-wrap">
<div class="page">
    @php
        $settings = \App\Models\Setting::whereIn('key', ['company_name','company_name_ar','company_logo','company_email','company_phone','currency_symbol'])
            ->pluck('value', 'key')->toArray();
        $logoData = pdf_logo_data($settings['company_logo'] ?? null);
        $companyName = $settings['company_name_ar'] ?? $settings['company_name'] ?? config('app.name');
        $currency = $settings['currency_symbol'] ?? 'ر.س';
    @endphp

    <div class="header">
        <div class="brand">
            @if($logoData)<img src="{{ $logoData }}" alt="Logo">@endif
            <h1>{{ $companyName }}</h1>
            <p>{{ $settings['company_email'] ?? '' }}{{ isset($settings['company_phone']) && $settings['company_phone'] ? ' | '.$settings['company_phone'] : '' }}</p>
        </div>
        <div class="title-box">
            <h2>عرض سعر</h2>
            <p>رقم: {{ $quote->quote_number }}</p>
            <p>التاريخ: {{ $quote->created_at?->format('Y-m-d') }}</p>
            @if($quote->valid_until)<p>صالح حتى: {{ $quote->valid_until->format('Y-m-d') }}</p>@endif
        </div>
    </div>

    <hr class="hr">

    <div class="info-box">
        <div class="label">العميل</div>
        <div class="value">{{ $quote->client?->name }}</div>
        @if($quote->client?->company_name)<div class="value" style="font-weight:400;font-size:13px">{{ $quote->client->company_name }}</div>@endif
        @if($quote->client?->email)<div style="font-size:12px;color:#6b7280;margin-top:4px">{{ $quote->client->email }}</div>@endif
    </div>

    <h3 style="font-size:14px;color:#1f2937;margin-bottom:4px">{{ $quote->subject }}</h3>
    @if($quote->description)<p style="font-size:13px;color:#6b7280;margin-bottom:12px;white-space:pre-line">{{ $quote->description }}</p>@endif

    <div class="table-wrap">
    <table>
        <thead><tr><th>الوصف</th><th style="text-align:center">الكمية</th><th style="text-align:left">سعر الوحدة</th><th style="text-align:left">الإجمالي</th></tr></thead>
        <tbody>
            @foreach($quote->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td style="text-align:center">{{ $item->quantity }}</td>
                    <td style="text-align:left">{{ number_format($item->unit_price, 2) }} {{ $currency }}</td>
                    <td style="text-align:left">{{ number_format($item->total, 2) }} {{ $currency }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    </div>

    <table class="totals">
        <tr><td style="text-align:left;color:#6b7280">الإجمالي الفرعي</td><td style="text-align:right">{{ number_format($quote->subtotal, 2) }} {{ $currency }}</td></tr>
        <tr><td style="text-align:left;color:#6b7280">الضريبة</td><td style="text-align:right">{{ number_format($quote->tax_amount, 2) }} {{ $currency }}</td></tr>
        <tr><td style="text-align:left;color:#6b7280">الخصم{{ $quote->discount_type === 'percentage' ? ' ('.($quote->discount_value ?? $quote->discount_percentage).'%)' : '' }}</td><td style="text-align:right">-{{ number_format($quote->discount_amount, 2) }} {{ $currency }}</td></tr>
        <tr class="final"><td style="text-align:left">الإجمالي</td><td style="text-align:right">{{ number_format($quote->total, 2) }} {{ $currency }}</td></tr>
    </table>

    @if($quote->notes || $quote->terms_and_conditions)
    <div class="notes">
        @if($quote->notes)
            <h3>{{ __('finance.notes') }}</h3>
            @foreach(text_lines($quote->notes) as $line)
                <p>{{ $line }}</p>
            @endforeach
        @endif
        @if($quote->terms_and_conditions)
            <h3 style="margin-top:10px">{{ __('finance.terms_and_conditions') }}</h3>
            @foreach(text_lines($quote->terms_and_conditions) as $line)
                <p>{{ $line }}</p>
            @endforeach
        @endif
    </div>
    @endif

    @if(session('success'))
    <div style="background:#ecfdf5;border:1px solid #a7f3d0;border-radius:10px;padding:16px 20px;margin-top:20px;text-align:center;color:#065f46;font-weight:600">
        {{ session('success') }}
    </div>
    @endif
    @if(session('info'))
    <div style="background:#fef3c7;border:1px solid #fde68a;border-radius:10px;padding:16px 20px;margin-top:20px;text-align:center;color:#92400e;font-weight:600">
        {{ session('info') }}
    </div>
    @endif

    @if($quote->status === 'approved')
    <div style="background:#d1fae5;border:1px solid #a7f3d0;border-radius:10px;padding:16px 20px;margin-top:20px;text-align:center;color:#065f46">
        <strong style="font-size:16px"><i class="fas fa-check-circle" style="margin-inline-end:6px"></i>تم اعتماد هذا العرض</strong>
    </div>
    @elseif($quote->status === 'rejected')
    <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:16px 20px;margin-top:20px;text-align:center;color:#991b1b">
        <strong style="font-size:16px">تم طلب تعديل على هذا العرض</strong>
        @if($quote->rejection_reason)
        <p style="margin-top:8px;font-size:13px;color:#7f1d1d">{{ $quote->rejection_reason }}</p>
        @endif
    </div>
    @endif

    <div class="actions">
        <a href="{{ route('quotes.public-pdf', $quote->public_token) }}" class="btn" style="background:#6b7280">
            تحميل PDF
        </a>
        @if($quote->status === 'sent')
        <button type="button" class="btn" style="border:none;cursor:pointer;background:#d97706;margin-inline-start:10px" onclick="toggleModificationForm()">
            <i class="fas fa-pencil-alt" style="margin-inline-end:6px"></i>{{ __('finance.request_modification') }}
        </button>
        @endif
    </div>

    @if($quote->status === 'sent')
    <div id="modificationForm" style="display:none;margin-top:16px;padding:16px;background:#fffbeb;border:1px solid #fde68a;border-radius:10px">
        <form action="{{ route('quotes.public-reject', $quote->public_token) }}" method="POST">
            @csrf
            <label style="display:block;font-size:13px;color:#92400e;margin-bottom:6px;font-weight:600">{{ __('finance.modification_notes') }}</label>
            <textarea name="rejection_reason" rows="4" style="width:100%;border:1px solid #d1d5db;border-radius:8px;padding:10px;font-size:13px;font-family:inherit" required></textarea>
            <div style="margin-top:10px;display:flex;gap:8px;justify-content:center">
                <button type="button" style="padding:8px 20px;border:1px solid #d1d5db;border-radius:8px;background:#fff;cursor:pointer;font-size:13px" onclick="toggleModificationForm()">إلغاء</button>
                <button type="submit" style="padding:8px 20px;border:none;border-radius:8px;background:#d97706;color:#fff;cursor:pointer;font-size:13px;font-weight:600">إرسال طلب التعديل</button>
            </div>
        </form>
    </div>
    <script>
    function toggleModificationForm() {
        var f = document.getElementById('modificationForm');
        f.style.display = f.style.display === 'none' ? 'block' : 'none';
    }
    </script>
    @endif
</div>
</div>
<script>
(function(){
    var p = document.querySelector('.page');
    var w = document.getElementById('page-wrap');
    function fit(){
        var pw = 210, vw = window.innerWidth, gap = vw > 500 ? 0 : 6;
        if(vw < pw){
            var s = (vw - gap * 2) / pw;
            p.style.transform = 'scale('+s+')';
            p.style.transformOrigin = 'top center';
            p.style.margin = '0 auto';
            var h = p.scrollHeight * s;
            p.style.marginBottom = (-(p.scrollHeight - h)) + 'px';
            w.style.padding = gap + 'px';
        } else {
            p.style.transform = 'none';
            p.style.marginBottom = '0';
            w.style.padding = '16px 0';
        }
    }
    fit();
    window.addEventListener('resize', fit);
    window.addEventListener('load', fit);
})();
</script>
</body>
</html>