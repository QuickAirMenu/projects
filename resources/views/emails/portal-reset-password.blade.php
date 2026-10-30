<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ is_rtl() ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ app()->getLocale() === 'ar' ? 'إعادة تعيين كلمة المرور' : 'Reset Password' }}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f8fafc; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f8fafc; padding: 40px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                    <tr>
                        <td style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); padding: 32px 40px; text-align: center;">
                            @php
                                $company = $settings['company_name'] ?? config('app.name');
                                $emailLogo = workspace_config('company_logo');
                                if ($emailLogo && !str_starts_with($emailLogo, 'http')) {
                                    $emailLogo = url($emailLogo);
                                }
                            @endphp
                            @if($emailLogo)
                                <div style="background: #ffffff; border-radius: 14px; padding: 10px 20px; margin: 0 auto 16px; display: inline-block;">
                                    <img src="{{ $emailLogo }}" alt="{{ $company }}" style="max-height: 50px; width: auto; display: block;">
                                </div>
                            @else
                                <div style="width: 56px; height: 56px; background: #ffffff; border-radius: 14px; margin: 0 auto 16px; line-height: 56px; font-size: 24px; font-weight: bold; color: #2563eb;">AM</div>
                            @endif
                            <h1 style="color: #ffffff; font-size: 24px; margin: 0;">{{ app()->getLocale() === 'ar' ? 'إعادة تعيين كلمة المرور' : 'Reset Your Password' }}</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 32px 40px;">
                            <p style="color: #1e293b; font-size: 16px; margin: 0;">{{ app()->getLocale() === 'ar' ? 'مرحباً' : 'Hello' }} {{ $client->name }},</p>
                            <p style="color: #64748b; font-size: 14px; line-height: 1.7; margin: 12px 0 0;">
                                {{ app()->getLocale() === 'ar'
                                    ? 'لقد تلقينا طلباً لإعادة تعيين كلمة المرور لحسابك في بوابة العميل. اضغط على الزر أدناه لإعادة تعيين كلمة المرور.'
                                    : 'We received a request to reset your password for your Client Portal account. Click the button below to reset your password.' }}
                            </p>
                            <p style="color: #64748b; font-size: 14px; line-height: 1.7; margin: 12px 0 0;">
                                {{ app()->getLocale() === 'ar'
                                    ? 'هذا الرابط صالح لمدة ساعتين فقط.'
                                    : 'This link is valid for 2 hours only.' }}
                            </p>
                            <div style="text-align: center; margin: 28px 0;">
                                <a href="{{ route('portal.reset-password', $token) }}"
                                   style="display: inline-block; background-color: #2563eb; color: #ffffff; text-decoration: none; padding: 14px 36px; border-radius: 10px; font-weight: 600; font-size: 14px;">
                                    {{ app()->getLocale() === 'ar' ? 'إعادة تعيين كلمة المرور' : 'Reset Password' }}
                                </a>
                            </div>
                            <p style="color: #94a3b8; font-size: 13px; line-height: 1.6; margin: 16px 0 0;">
                                {{ app()->getLocale() === 'ar'
                                    ? 'إذا لم تطلب إعادة تعيين كلمة المرور، يمكنك تجاهل هذا البريد الإلكتروني.'
                                    : 'If you did not request a password reset, you can ignore this email.' }}
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #f1f5f9; padding: 24px 40px; text-align: center;">
                            <p style="color: #64748b; font-size: 13px; margin: 0;">{{ $company }}</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>