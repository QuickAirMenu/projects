@extends('layouts.app')
@section('title', __('help.title'))

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">{{ __('help.title') }}</h1>
    <p class="text-gray-500 mt-1">{{ __('help.subtitle', ['app' => config('app.name')]) }}</p>
</div>

<!-- Search -->
<div class="mb-8 max-w-2xl">
    <div class="relative">
        <i class="fas fa-search absolute {{ is_rtl() ? 'right-3' : 'left-3' }} top-1/2 -translate-y-1/2 text-gray-400"></i>
        <input type="text" id="helpSearch" placeholder="{{ __('help.search_placeholder') }}"
               class="w-full {{ is_rtl() ? 'pr-10' : 'pl-10' }} border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
</div>

<!-- Help Sections Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5" id="helpSections">
    @php $sections = [
        ['id' => 'getting-started', 'icon' => 'fa-rocket', 'color' => 'blue', 'title' => __('help.getting_started'),
         'items' => [
             ['q' => __('help.gs_q1'), 'a' => __('help.gs_a1')],
             ['q' => __('help.gs_q2'), 'a' => __('help.gs_a2')],
             ['q' => __('help.gs_q3'), 'a' => __('help.gs_a3')],
         ]],
        ['id' => 'clients', 'icon' => 'fa-users', 'color' => 'green', 'title' => __('help.clients'),
         'items' => [
             ['q' => __('help.clients_q1'), 'a' => __('help.clients_a1')],
             ['q' => __('help.clients_q2'), 'a' => __('help.clients_a2')],
             ['q' => __('help.clients_q3'), 'a' => __('help.clients_a3')],
         ]],
        ['id' => 'projects', 'icon' => 'fa-briefcase', 'color' => 'purple', 'title' => __('help.projects'),
         'items' => [
             ['q' => __('help.projects_q1'), 'a' => __('help.projects_a1')],
             ['q' => __('help.projects_q2'), 'a' => __('help.projects_a2')],
             ['q' => __('help.projects_q3'), 'a' => __('help.projects_a3')],
         ]],
        ['id' => 'tasks', 'icon' => 'fa-tasks', 'color' => 'amber', 'title' => __('help.tasks'),
         'items' => [
             ['q' => __('help.tasks_q1'), 'a' => __('help.tasks_a1')],
             ['q' => __('help.tasks_q2'), 'a' => __('help.tasks_a2')],
             ['q' => __('help.tasks_q3'), 'a' => __('help.tasks_a3')],
         ]],
        ['id' => 'finance', 'icon' => 'fa-file-invoice', 'color' => 'emerald', 'title' => __('help.finance'),
         'items' => [
             ['q' => __('help.finance_q1'), 'a' => __('help.finance_a1')],
             ['q' => __('help.finance_q2'), 'a' => __('help.finance_a2')],
             ['q' => __('help.finance_q3'), 'a' => __('help.finance_a3')],
             ['q' => __('help.finance_q4'), 'a' => __('help.finance_a4')],
             ['q' => __('help.finance_q5'), 'a' => __('help.finance_a5')],
         ]],
        ['id' => 'reports', 'icon' => 'fa-chart-bar', 'color' => 'rose', 'title' => __('help.reports'),
         'items' => [
             ['q' => __('help.reports_q1'), 'a' => __('help.reports_a1')],
             ['q' => __('help.reports_q2'), 'a' => __('help.reports_a2')],
         ]],
    ]; @endphp

    @foreach($sections as $section)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 help-section" data-section="{{ $section['id'] }}">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-xl bg-{{ $section['color'] }}-100 flex items-center justify-center">
                <i class="fas {{ $section['icon'] }} text-{{ $section['color'] }}-600"></i>
            </div>
            <h2 class="font-bold text-lg text-gray-800">{{ $section['title'] }}</h2>
        </div>
        <div class="space-y-3">
            @foreach($section['items'] as $item)
            <details class="group border border-gray-100 rounded-xl">
                <summary class="px-4 py-3 cursor-pointer text-sm font-medium text-gray-700 hover:text-blue-600 list-none flex items-center justify-between">
                    {{ $item['q'] }}
                    <i class="fas fa-chevron-down text-gray-400 group-open:rotate-180 transition-transform"></i>
                </summary>
                <div class="px-4 pb-4 text-sm text-gray-500 leading-relaxed">{{ $item['a'] }}</div>
            </details>
            @endforeach
        </div>
    </div>
    @endforeach
</div>

@push('scripts')
<script>
document.getElementById('helpSearch').addEventListener('input', function() {
    const query = this.value.toLowerCase();
    document.querySelectorAll('.help-section').forEach(section => {
        const text = section.textContent.toLowerCase();
        section.style.display = text.includes(query) ? '' : 'none';
    });
});
</script>
@endpush
@endsection
