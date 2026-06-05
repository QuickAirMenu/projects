<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General
            ['key' => 'company_name',      'value' => 'Digital Business Solutions', 'type' => 'string',  'group' => 'general'],
            ['key' => 'company_name_ar',   'value' => 'حلول الأعمال الرقمية',         'type' => 'string',  'group' => 'general'],
            ['key' => 'company_email',     'value' => 'info@dbs.sa',                  'type' => 'string',  'group' => 'general'],
            ['key' => 'company_phone',     'value' => '+966500000000',                'type' => 'string',  'group' => 'general'],
            ['key' => 'company_address',   'value' => 'الرياض، المملكة العربية السعودية', 'type' => 'string', 'group' => 'general'],
            ['key' => 'company_website',   'value' => 'https://dbs.sa',              'type' => 'string',  'group' => 'general'],
            ['key' => 'default_language',  'value' => 'ar',                           'type' => 'string',  'group' => 'general'],
            ['key' => 'default_timezone',  'value' => 'Asia/Riyadh',                 'type' => 'string',  'group' => 'general'],
            ['key' => 'date_format',       'value' => 'Y-m-d',                        'type' => 'string',  'group' => 'general'],

            // Finance
            ['key' => 'default_tax_rate',     'value' => '15',   'type' => 'number',  'group' => 'finance'],
            ['key' => 'currency',             'value' => 'SAR',  'type' => 'string',  'group' => 'finance'],
            ['key' => 'currency_symbol',      'value' => 'ر.س',  'type' => 'string',  'group' => 'finance'],
            ['key' => 'invoice_prefix',       'value' => 'INV',  'type' => 'string',  'group' => 'finance'],
            ['key' => 'quote_prefix',         'value' => 'QT',   'type' => 'string',  'group' => 'finance'],
            ['key' => 'payment_terms',        'value' => 'الدفع خلال 30 يوم من تاريخ الفاتورة', 'type' => 'string', 'group' => 'finance'],
            ['key' => 'bank_account_name',    'value' => 'Digital Business Solutions', 'type' => 'string', 'group' => 'finance'],
            ['key' => 'bank_iban',            'value' => 'SA0000000000000000000000',  'type' => 'string',  'group' => 'finance'],

            // Notifications
            ['key' => 'notify_task_assigned',   'value' => '1', 'type' => 'boolean', 'group' => 'notifications'],
            ['key' => 'notify_deadline_days',   'value' => '2', 'type' => 'number',  'group' => 'notifications'],
            ['key' => 'notify_project_status',  'value' => '1', 'type' => 'boolean', 'group' => 'notifications'],
            ['key' => 'email_notifications',    'value' => '0', 'type' => 'boolean', 'group' => 'notifications'],

            // System
            ['key' => 'system_version',     'value' => '1.0.0', 'type' => 'string', 'group' => 'system'],
            ['key' => 'maintenance_mode',   'value' => '0',     'type' => 'boolean', 'group' => 'system'],
            ['key' => 'max_upload_size_mb', 'value' => '20',    'type' => 'number',  'group' => 'system'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
