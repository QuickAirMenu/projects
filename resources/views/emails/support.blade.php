<!DOCTYPE html>
<html dir="rtl">
<head>
    <meta charset="utf-8">
    <style>
        body{margin:0;padding:0;background:#f4f4f5;font-family:Tahoma,'Segoe UI',Arial,sans-serif}
        .wrap{max-width:580px;margin:0 auto;padding:24px 16px}
        .card{background:#fff;border-radius:14px;padding:28px}
        h1{font-size:18px;color:#1f2937;margin:0 0 16px}
        p{font-size:14px;color:#4b5563;margin:0 0 10px;line-height:1.7}
        .meta{background:#f9fafb;border-radius:10px;padding:14px;margin:16px 0;font-size:13px;color:#6b7280}
        .meta strong{color:#374151}
        .box{background:#f0f7ff;border-radius:10px;padding:16px;margin:16px 0;border-right:3px solid #2563eb}
        .box p{color:#1e40af;margin:0;white-space:pre-line}
        hr{border:0;border-top:1px solid #e5e7eb;margin:20px 0}
        .footer{text-align:center;color:#9ca3af;font-size:12px;padding-top:16px}
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <h1><i class="fas fa-inbox"></i> رسالة دعم فني جديدة</h1>
        <div class="meta">
            <p><strong>المرسل:</strong> {{ $senderName }}</p>
            <p><strong>البريد:</strong> {{ $senderEmail }}</p>
            <p><strong>الموضوع:</strong> {{ $subjectText }}</p>
        </div>
        <div class="box">
            <p>{{ $messageText }}</p>
        </div>
        <hr>
        <p style="font-size:13px;color:#9ca3af">تم إرسال هذه الرسالة عبر نموذج الدعم الفني في النظام.</p>
    </div>
</div>
</body>
</html>
