<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\MailConfigService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->groupBy('group')->map(fn ($group) => $group->pluck('value', 'key'));

        $host = trim((string) ($settings['mail']['mail_host'] ?? ''));
        $testedAt = $settings['mail']['mail_test_at'] ?? null;
        $mailStatus = [
            'configured' => $host !== '',
            'tested' => filled($testedAt),
            'ok' => filled($testedAt) && ($settings['mail']['mail_test_ok'] ?? null) === '1',
            'at' => $testedAt,
            'error' => $settings['mail']['mail_test_error'] ?? null,
        ];

        return view('settings.index', compact('settings', 'mailStatus'));
    }

    public function testMail(Request $request)
    {
        app(MailConfigService::class)->applyDynamicMailConfig();

        $recipient = Setting::query()->where('key', 'mail_from_address')->value('value')
            ?: Setting::query()->where('key', 'company_email')->value('value')
            ?: $request->user()?->email;

        if (blank(config('mail.mailers.smtp.host'))) {
            $this->recordMailTest(false, __('messages.mail_not_configured'));

            return back()->with('error', __('messages.mail_not_configured'));
        }

        if (blank($recipient)) {
            $this->recordMailTest(false, __('messages.mail_test_no_recipient'));

            return back()->with('error', __('messages.mail_test_no_recipient'));
        }

        try {
            Mail::raw(__('messages.mail_test_body'), function ($message) use ($recipient) {
                $message->to($recipient)->subject(__('messages.mail_test_subject'));
            });

            $this->recordMailTest(true, null);

            return back()->with('success', __('messages.mail_test_success', ['email' => $recipient]));
        } catch (\Throwable $e) {
            report($e);
            $this->recordMailTest(false, mb_substr($e->getMessage(), 0, 500));

            return back()->with('error', __('messages.mail_test_failed'));
        }
    }

    public function update(Request $request)
    {
        $request->validate([
            'company_logo' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $groupMap = [
            'company_name' => 'general',
            'company_name_ar' => 'general',
            'company_email' => 'general',
            'company_phone' => 'general',
            'company_website' => 'general',
            'company_address' => 'general',
            'company_logo' => 'general',
            'default_tax_rate' => 'finance',
            'reminder_interval_days' => 'finance',
            'currency' => 'finance',
            'currency_symbol' => 'finance',
            'bank_account_name' => 'finance',
            'bank_iban' => 'finance',
            'mail_host' => 'mail',
            'mail_port' => 'mail',
            'mail_encryption' => 'mail',
            'mail_username' => 'mail',
            'mail_password' => 'mail',
            'mail_from_address' => 'mail',
            'mail_from_name' => 'mail',
            'portal_show_invoices' => 'portal',
            'portal_show_quotes' => 'portal',
            'vat_number' => 'general',
            'commercial_registration' => 'general',
        ];

        if ($request->hasFile('company_logo')) {
            $oldLogo = Setting::where('key', 'company_logo')->value('value');
            $path = $request->file('company_logo')->store('settings', 'public');

            if ($oldLogo && $oldLogo !== $path && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }
            Setting::updateOrCreate(
                ['key' => 'company_logo'],
                ['value' => $path, 'group' => 'general']
            );
        }

        foreach ($request->except(['_token', '_method', 'company_logo']) as $key => $value) {
            if ($key === 'mail_password' && blank($value)) {
                continue;
            }
            if ($key === 'mail_password') {
                $value = Crypt::encryptString($value);
            }
            if ($key === 'company_website' && $value && ! str_starts_with($value, 'http')) {
                $value = 'https://'.$value;
            }
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'group' => $groupMap[$key] ?? 'general']
            );
        }

        $checkboxKeys = ['portal_show_invoices', 'portal_show_quotes'];
        if ($request->hasAny($checkboxKeys)) {
            foreach ($checkboxKeys as $key) {
                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $request->boolean($key) ? '1' : '0', 'group' => $groupMap[$key]]
                );
            }
        }

        return redirect()->route('settings.index')
            ->with('success', __('messages.updated_successfully'));
    }

    protected function recordMailTest(bool $ok, ?string $error): void
    {
        foreach ([
            'mail_test_ok' => $ok ? '1' : '0',
            'mail_test_at' => now()->toDateTimeString(),
            'mail_test_error' => $error,
        ] as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value, 'type' => 'string', 'group' => 'mail']);
        }
    }
}
