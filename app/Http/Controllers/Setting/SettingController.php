<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->groupBy('group')->map(fn($group) => $group->pluck('value', 'key'));
        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        foreach ($request->except(['_token', '_method']) as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
        return redirect()->route('settings.index')
            ->with('success', __('messages.updated_successfully'));
    }
}
