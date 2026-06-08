<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>دعوة للانضمام</title>
</head>
<body style="margin:0;padding:0;background-color:#f3f4f6;font-family:'Segoe UI',Tahoma,Arial,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f4f6;padding:40px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="560" cellpadding="0" cellspacing="0" style="background-color:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.08);">
                    <!-- Header -->
                    <tr>
                        <td style="background-color:#2563eb;padding:32px 40px;text-align:center;">
                            <h1 style="color:#ffffff;font-size:22px;font-weight:700;margin:0;">{{ config('app.name') }}</h1>
                        </td>
                    </tr>
                    <!-- Body -->
                    <tr>
                        <td style="padding:40px;">
                            <p style="font-size:16px;color:#374151;margin:0 0 16px;">مرحباً <strong>{{ $client->name }}</strong>،</p>
                            <p style="font-size:15px;color:#4b5563;line-height:1.7;margin:0 0 24px;">
                                يسعدنا انضمامك إلينا. يمكنك الآن إعداد حسابك في بوابة العميل للاطلاع على مشاريعك وعروض السعر والفواتير ومتابعة كل جديد.
                            </p>
                            <!-- CTA Button -->
                            <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 auto 24px;">
                                <tr>
                                    <td align="center" style="background-color:#2563eb;border-radius:12px;padding:12px 32px;">
                                        <a href="{{ $setupUrl }}" style="color:#ffffff;font-size:16px;font-weight:600;text-decoration:none;display:inline-block;">
                                            إعداد حسابي
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            <!-- Fallback link -->
                            <p style="font-size:13px;color:#9ca3af;text-align:center;margin:0 0 24px;direction:ltr;">
                                <a href="{{ $setupUrl }}" style="color:#2563eb;">{{ $setupUrl }}</a>
                            </p>
                            <hr style="border:none;border-top:1px solid #e5e7eb;margin:24px 0;">
                            <p style="font-size:13px;color:#9ca3af;text-align:center;margin:0;">
                                هذا الرابط صالح لمدة 7 أيام. إذا لم تقم بإنشاء الحساب خلال هذه المدة، يرجى التواصل معنا لإعادة إرسال الدعوة.
                            </p>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style="background-color:#f9fafb;padding:20px 40px;text-align:center;">
                            <p style="font-size:12px;color:#9ca3af;margin:0;">{{ config('app.name') }} &mdash; جميع الحقوق محفوظة</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
