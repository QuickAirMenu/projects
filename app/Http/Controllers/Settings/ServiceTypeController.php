<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\ServiceType;
use Illuminate\Http\Request;

class ServiceTypeController extends Controller
{
    public function index()
    {
        $serviceTypes = ServiceType::orderBy('sort_order')->get();
        return view('settings.service-types', compact('serviceTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name_ar'        => 'required|string|max:255',
            'name_en'        => 'required|string|max:255',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'icon'           => 'nullable|string|max:50',
            'color'          => 'required|string|max:7',
            'is_active'      => 'boolean',
        ]);

        ServiceType::create($request->all());

        return redirect()->route('settings.service-types.index')
            ->with('success', __('common.created_successfully'));
    }

    public function edit(ServiceType $serviceType)
    {
        return view('settings.service-types', compact('serviceType'));
    }

    public function update(Request $request, ServiceType $serviceType)
    {
        $request->validate([
            'name_ar'   => 'required|string|max:255',
            'name_en'   => 'required|string|max:255',
            'color'     => 'required|string|max:7',
            'is_active' => 'boolean',
        ]);

        $serviceType->update($request->all());

        return redirect()->route('settings.service-types.index')
            ->with('success', __('common.updated_successfully'));
    }

    public function destroy(ServiceType $serviceType)
    {
        $serviceType->delete();
        return redirect()->route('settings.service-types.index')
            ->with('success', __('common.deleted_successfully'));
    }
}
