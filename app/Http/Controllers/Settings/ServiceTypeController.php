<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\ServiceType;
use App\Models\ServiceTypeItem;
use Illuminate\Http\Request;

class ServiceTypeController extends Controller
{
    public function index()
    {
        $serviceTypes = ServiceType::with(['defaultItems' => fn ($q) => $q->orderBy('sort_order')])->orderBy('sort_order')->get();

        return view('settings.service-types', compact('serviceTypes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'color' => 'required|string|max:7',
            'is_active' => 'boolean',
        ]);

        ServiceType::create($data);

        return redirect()->route('settings.service-types.index')
            ->with('success', __('common.created_successfully'));
    }

    public function edit(ServiceType $serviceType)
    {
        return view('settings.service-types', compact('serviceType'));
    }

    public function update(Request $request, ServiceType $serviceType)
    {
        $data = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'color' => 'required|string|max:7',
            'is_active' => 'boolean',
        ]);

        $serviceType->update($data);

        return redirect()->route('settings.service-types.index')
            ->with('success', __('common.updated_successfully'));
    }

    public function destroy(ServiceType $serviceType)
    {
        $serviceType->delete();

        return redirect()->route('settings.service-types.index')
            ->with('success', __('common.deleted_successfully'));
    }

    public function storeItem(Request $request, ServiceType $serviceType)
    {
        $data = $request->validate([
            'description' => 'required|string',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        ServiceTypeItem::create([
            'service_type_id' => $serviceType->id,
            'description' => $data['description'],
            'quantity' => $data['quantity'],
            'unit_price' => $data['unit_price'],
            'sort_order' => $data['sort_order'] ?? ($serviceType->defaultItems()->max('sort_order') + 1),
        ]);

        return back()->with('success', __('common.created_successfully'));
    }

    public function updateItem(Request $request, ServiceType $serviceType, ServiceTypeItem $item)
    {
        abort_unless($item->service_type_id === $serviceType->id, 404);

        $data = $request->validate([
            'description' => 'required|string',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
        ]);

        $item->update($data);

        return back()->with('success', __('common.updated_successfully'));
    }

    public function destroyItem(ServiceType $serviceType, ServiceTypeItem $item)
    {
        abort_unless($item->service_type_id === $serviceType->id, 404);
        $item->delete();

        return back()->with('success', __('common.deleted_successfully'));
    }
}
