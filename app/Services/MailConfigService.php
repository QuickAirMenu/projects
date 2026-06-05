<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;

class MailConfigService
{
    public function applyDynamicMailConfig(): void
    {
        $settings = Setting::query()->pluck('value', 'key');

        $password = $settings['mail_password'] ?? null;
        if (is_string($password) && $password !== '') {
            try {
                $password = Crypt::decryptString($password);
            } catch (\Throwable) {
                // Keep raw value if it is already plain text.
            }
        }

        Config::set('mail.default', 'smtp');

        if (!blank($settings['mail_host'] ?? null)) {
            Config::set('mail.mailers.smtp.host', $settings['mail_host']);
        }
        if (!blank($settings['mail_port'] ?? null)) {
            Config::set('mail.mailers.smtp.port', (int) $settings['mail_port']);
        }
        if (!blank($settings['mail_encryption'] ?? null)) {
            Config::set('mail.mailers.smtp.encryption', $settings['mail_encryption']);
        }
        if (!blank($settings['mail_username'] ?? null)) {
            Config::set('mail.mailers.smtp.username', $settings['mail_username']);
        }
        if (!blank($password)) {
            Config::set('mail.mailers.smtp.password', $password);
        }
        if (!blank($settings['mail_from_address'] ?? null)) {
            Config::set('mail.from.address', $settings['mail_from_address']);
        }
        if (!blank($settings['mail_from_name'] ?? null)) {
            Config::set('mail.from.name', $settings['mail_from_name']);
        }
    }
}
