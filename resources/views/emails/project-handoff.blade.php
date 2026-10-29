<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ is_rtl() ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('mail.handoff_subject') }}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f8fafc; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f8fafc; padding: 40px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">

                    {{-- Header --}}
                    <tr>
                        <td style="background: linear-gradient(135deg, #059669 0%, #10b981 100%); padding: 32px 40px; text-align: center;">
                            @php
                                $companyName = $settings['company_name_display'] ?? 'Air Menu';
                                $handoffLogo = workspace_config('company_logo');
                                if ($handoffLogo && !str_starts_with($handoffLogo, 'http')) {
                                    $handoffLogo = url($handoffLogo);
                                }
                            @endphp
                            @if($handoffLogo)
                                <div style="background: #ffffff; border-radius: 14px; padding: 10px 20px; margin: 0 auto 16px; display: inline-block;">
                                    <img src="{{ $handoffLogo }}" alt="{{ $companyName }}" style="max-height: 50px; width: auto; display: block;">
                                </div>
                            @else
                                <div style="width: 56px; height: 56px; background: #ffffff; border-radius: 14px; margin: 0 auto 16px; line-height: 56px; font-size: 24px; font-weight: bold; color: #059669;">AM</div>
                            @endif
                            <h1 style="color: #ffffff; font-size: 24px; margin: 0;">{{ __('mail.handoff_subject') }}</h1>
                            <p style="color: #d1fae5; font-size: 14px; margin: 8px 0 0;">{{ $project->name }}</p>
                        </td>
                    </tr>

                    {{-- Greeting --}}
                    <tr>
                        <td style="padding: 32px 40px 16px;">
                            <p style="color: #1e293b; font-size: 16px; margin: 0;">{{ __('mail.handoff_greeting', ['name' => $project->client?->name ?? '']) }}</p>
                            <p style="color: #64748b; font-size: 14px; line-height: 1.7; margin: 12px 0 0;">{{ __('mail.handoff_message') }}</p>
                        </td>
                    </tr>

                    {{-- Project Info --}}
                    <tr>
                        <td style="padding: 0 40px 24px;">
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f0fdf4; border-radius: 12px; border: 1px solid #bbf7d0;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <h3 style="color: #166534; font-size: 14px; margin: 0 0 12px; text-transform: uppercase; letter-spacing: 0.5px;">{{ __('mail.handoff_project_info') }}</h3>
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="padding: 4px 0; color: #64748b; font-size: 13px;">{{ __('mail.handoff_project_number') }}</td>
                                                <td style="padding: 4px 0; color: #1e293b; font-size: 13px; font-weight: 600; text-align: right;">{{ $project->project_number }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 4px 0; color: #64748b; font-size: 13px;">{{ __('project.progress') }}</td>
                                                <td style="padding: 4px 0; color: #1e293b; font-size: 13px; font-weight: 600; text-align: right;">{{ $project->progress_percentage }}%</td>
                                            </tr>
                                            @if($project->handed_off_at)
                                            <tr>
                                                <td style="padding: 4px 0; color: #64748b; font-size: 13px;">{{ __('project.handoff_date') }}</td>
                                                <td style="padding: 4px 0; color: #1e293b; font-size: 13px; font-weight: 600; text-align: right;">{{ $project->handed_off_at->format('Y-m-d') }}</td>
                                            </tr>
                                            @endif
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Portal Link --}}
                    @if($hasPortalAccount)
                    <tr>
                        <td style="padding: 0 40px 24px;" align="center">
                            <a href="{{ route('portal.dashboard') }}" style="display: inline-block; background-color: #059669; color: #ffffff; text-decoration: none; padding: 12px 32px; border-radius: 10px; font-weight: 600; font-size: 14px;">{{ __('mail.handoff_portal_link') }}</a>
                        </td>
                    </tr>
                    @endif

                    {{-- Original Quote --}}
                    @if($project->quotes->count())
                    <tr>
                        <td style="padding: 0 40px 24px;">
                            <h3 style="color: #1e293b; font-size: 14px; margin: 0 0 12px; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px;">{{ __('mail.handoff_quote_summary') }}</h3>
                            @foreach($project->quotes as $quote)
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 8px;">
                                <tr>
                                    <td style="padding: 8px 12px; background-color: #f8fafc; border-radius: 8px; border-left: 3px solid #3b82f6;">
                                        <span style="color: #1e293b; font-weight: 600; font-size: 13px;">{{ $quote->quote_number }}</span>
                                        @if($quote->subject)
                                            <span style="color: #94a3b8; font-size: 13px;"> — {{ $quote->subject }}</span>
                                        @endif
                                        <span style="float: {{ is_rtl() ? 'left' : 'right' }}; color: #1e293b; font-weight: 600; font-size: 13px;">{{ number_format($quote->total, 2) }} {{ __('messages.currency') }}</span>
                                    </td>
                                </tr>
                            </table>
                            @endforeach
                        </td>
                    </tr>
                    @endif

                    {{-- Invoices --}}
                    @if($showInvoices && $project->invoices->count())
                    <tr>
                        <td style="padding: 0 40px 24px;">
                            <h3 style="color: #1e293b; font-size: 14px; margin: 0 0 12px; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px;">{{ __('mail.handoff_invoice_summary') }}</h3>
                            @foreach($project->invoices as $invoice)
                            @php
                                $statusColor = ['paid'=>'#059669','partially_paid'=>'#d97706','overdue'=>'#dc2626','unpaid'=>'#6b7280'][$invoice->payment_status] ?? '#6b7280';
                            @endphp
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 8px;">
                                <tr>
                                    <td style="padding: 8px 12px; background-color: #f8fafc; border-radius: 8px; border-left: 3px solid {{ $statusColor }};">
                                        <span style="color: #1e293b; font-weight: 600; font-size: 13px;">{{ $invoice->invoice_number }}</span>
                                        <span style="display: inline-block; background-color: {{ $statusColor }}20; color: {{ $statusColor }}; font-size: 11px; padding: 2px 8px; border-radius: 12px; margin-{{ is_rtl() ? 'right' : 'left' }}: 8px;">{{ __('finance.status_'.$invoice->payment_status) }}</span>
                                        <span style="float: {{ is_rtl() ? 'left' : 'right' }}; color: #1e293b; font-weight: 600; font-size: 13px;">{{ number_format($invoice->total, 2) }} {{ __('messages.currency') }}</span>
                                    </td>
                                </tr>
                            </table>
                            @endforeach
                        </td>
                    </tr>
                    @endif

                    {{-- Attachments --}}
                    @if($project->attachments->count())
                    <tr>
                        <td style="padding: 0 40px 24px;">
                            <h3 style="color: #1e293b; font-size: 14px; margin: 0 0 12px; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px;">{{ __('mail.handoff_attachments') }}</h3>
                            @foreach($project->attachments as $attachment)
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 4px;">
                                <tr>
                                    <td style="padding: 6px 12px; background-color: #f8fafc; border-radius: 6px;">
                                        <span style="color: #64748b; font-size: 13px;">&#128206;</span>
                                        <span style="color: #1e293b; font-size: 13px; margin-{{ is_rtl() ? 'right' : 'left' }}: 8px;">{{ $attachment->file_name }}</span>
                                        <span style="color: #94a3b8; font-size: 12px;">({{ round($attachment->file_size / 1024) }} KB)</span>
                                    </td>
                                </tr>
                            </table>
                            @endforeach
                        </td>
                    </tr>
                    @endif

                    {{-- Handoff Notes --}}
                    @if($project->handoff_notes)
                    <tr>
                        <td style="padding: 0 40px 24px;">
                            <h3 style="color: #1e293b; font-size: 14px; margin: 0 0 8px; text-transform: uppercase; letter-spacing: 0.5px;">{{ __('mail.handoff_notes') }}</h3>
                            <p style="color: #64748b; font-size: 14px; line-height: 1.6; margin: 0; background-color: #f8fafc; padding: 16px; border-radius: 8px;">{{ $project->handoff_notes }}</p>
                        </td>
                    </tr>
                    @endif

                    {{-- Footer --}}
                    <tr>
                        <td style="background-color: #f1f5f9; padding: 24px 40px; text-align: center;">
                            <p style="color: #64748b; font-size: 13px; margin: 0 0 8px;">{{ __('mail.handoff_footer') }}</p>
                            <p style="color: #475569; font-size: 13px; font-weight: 600; margin: 0;">{{ $companyName }}</p>
                            @if(!empty($settings['company_email']) || !empty($settings['company_phone']))
                            <p style="color: #94a3b8; font-size: 12px; margin: 4px 0 0;">
                                @if(!empty($settings['company_email'])){{ $settings['company_email'] }}@endif
                                @if(!empty($settings['company_phone']))@if(!empty($settings['company_email'])) &bull; @endif{{ $settings['company_phone'] }}@endif
                            </p>
                            @endif
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
