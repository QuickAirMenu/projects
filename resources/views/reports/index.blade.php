@extends('layouts.app')
@section('title', __('messages.reports'))

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">{{ __('messages.reports') }}</h1>
    <p class="text-gray-500 text-sm mt-1">نظرة عامة على أداء النظام</p>
</div>

{{-- Summary Stats --}}
<div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
    @foreach([
        ['label' => 'المشاريع',   'val' => $stats['projects'],             'color' => 'blue',  'icon' => 'fa-folder'],
        ['label' => 'العملاء',    'val' => $stats['clients'],              'color' => 'green', 'icon' => 'fa-users'],
        ['label' => 'المهام',     'val' => $stats['tasks'],                'color' => 'purple','icon' => 'fa-tasks'],
        ['label' => 'الفواتير',   'val' => number_format($stats['invoiced'],0).' ر.س', 'color' => 'yellow','icon' => 'fa-file-invoice'],
        ['label' => 'المحصّل',    'val' => number_format($stats['collected'],0).' ر.س','color' => 'teal',  'icon' => 'fa-money-bill'],
    ] as $c)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="w-10 h-10 rounded-xl bg-{{ $c['color'] }}-100 flex items-center justify-center mb-3">
            <i class="fas {{ $c['icon'] }} text-{{ $c['color'] }}-600"></i>
        </div>
        <p class="text-xl font-bold text-gray-800">{{ $c['val'] }}</p>
        <p class="text-xs text-gray-500 mt-1">{{ $c['label'] }}</p>
    </div>
    @endforeach
</div>

{{-- Report Links --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
    @foreach([
        ['route' => 'reports.projects',    'label' => 'تقرير المشاريع',   'desc' => 'حسب الحالة والأولوية', 'icon' => 'fa-folder-open', 'color' => 'blue'],
        ['route' => 'reports.tasks',       'label' => 'تقرير المهام',     'desc' => 'حسب الموظف والحالة',   'icon' => 'fa-clipboard-list', 'color' => 'purple'],
        ['route' => 'reports.finance',     'label' => 'التقرير المالي',   'desc' => 'الفواتير والمدفوعات',   'icon' => 'fa-chart-line', 'color' => 'green'],
        ['route' => 'reports.performance', 'label' => 'تقرير الأداء',     'desc' => 'أداء الفريق والإنتاجية','icon' => 'fa-tachometer-alt', 'color' => 'orange'],
    ] as $r)
    <a href="{{ route($r['route']) }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md hover:border-{{ $r['color'] }}-200 transition group">
        <div class="w-12 h-12 rounded-xl bg-{{ $r['color'] }}-100 group-hover:bg-{{ $r['color'] }}-200 flex items-center justify-center mb-4 transition">
            <i class="fas {{ $r['icon'] }} text-{{ $r['color'] }}-600 text-xl"></i>
        </div>
        <h3 class="font-semibold text-gray-800 mb-1">{{ $r['label'] }}</h3>
        <p class="text-xs text-gray-500">{{ $r['desc'] }}</p>
    </a>
    @endforeach
</div>
@endsection
