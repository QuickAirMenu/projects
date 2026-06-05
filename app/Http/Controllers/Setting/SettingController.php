<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->groupBy('group')->map(fn($group) => $group->pluck('value', 'key'));
        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'company_logo' => 'nullable|file|mimes:jpg,jpeg,png,webp,svg|max:2048',
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
        ];

        if ($request->hasFile('company_logo')) {
            $path = $request->file('company_logo')->store('settings', 'public');

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

            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'group' => $groupMap[$key] ?? 'general']
            );
        }
        return redirect()->route('settings.index')
            ->with('success', __('messages.updated_successfully'));
    }
}
