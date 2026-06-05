@extends('layouts.app')
@section('title', 'تسليم المشروع')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">تسليم المشروع</h1>
        <p class="text-gray-500 text-sm">{{ $project->name }} - {{ $project->client?->name }}</p>
    </div>
    <a href="{{ route('projects.show', $project) }}" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-xl text-sm">{{ __('messages.back') }}</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div><p class="text-gray-500">الميزانية</p><p class="font-semibold">{{ number_format($project->budget, 2) }}</p></div>
            <div><p class="text-gray-500">الإنفاق الفعلي</p><p class="font-semibold">{{ number_format($project->actual_cost, 2) }}</p></div>
            <div><p class="text-gray-500">نسبة الإنجاز</p><p class="font-semibold">{{ $project->progress_percentage }}%</p></div>
            <div><p class="text-gray-500">تاريخ التسليم</p><p class="font-semibold">{{ $project->handed_off_at?->format('Y-m-d H:i') ?? '-' }}</p></div>
        </div>

        <div>
            <h3 class="font-semibold mb-2">الفواتير</h3>
            <div class="space-y-2">
                @forelse($project->invoices as $invoice)
                    <div class="flex items-center justify-between border rounded-xl p-3 text-sm">
                        <span>{{ $invoice->invoice_number }}</span>
                        <span>{{ $invoice->payment_status }}</span>
                    </div>
                @empty
                    <p class="text-gray-500 text-sm">لا توجد فواتير</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 h-fit">
        <form action="{{ route('projects.handoff.complete', $project) }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">ملاحظات التسليم</label>
                <textarea name="handoff_notes" rows="5" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm">{{ old('handoff_notes') }}</textarea>
            </div>
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-xl text-sm">تسليم وإغلاق المشروع</button>
        </form>
    </div>
</div>
@endsection
