@extends('layouts.app')
@section('title', __('client.clients'))

@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ __('client.clients') }}</h1>
        <p class="text-gray-500 text-sm mt-1">{{ $stats['total'] }} {{ __('client.client') }}</p>
    </div>
    @can('create', App\Models\Client::class)
    <a href="{{ route('clients.create') }}" class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl transition font-medium">
        <i class="fas fa-plus"></i> {{ __('client.add_client') }}
    </a>
    @endcan
</div>


{{-- Stats --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    @foreach([
        ['label' => 'الإجمالي',  'val' => $stats['total'],    'color' => 'blue',   'icon' => 'fa-users'],
        ['label' => 'نشطون',     'val' => $stats['active'],   'color' => 'green',  'icon' => 'fa-user-check'],
        ['label' => 'VIP',       'val' => $stats['vip'],      'color' => 'yellow', 'icon' => 'fa-star'],
        ['label' => 'محتملون',   'val' => $stats['potential'],'color' => 'purple', 'icon' => 'fa-user-clock'],
    ] as $c)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-{{ $c['color'] }}-100 flex items-center justify-center">
            <i class="fas {{ $c['icon'] }} text-{{ $c['color'] }}-600 text-xl"></i>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-800">{{ $c['val'] }}</p>
            <p class="text-xs text-gray-500">{{ $c['label'] }}</p>
        </div>
    </div>
    @endforeach
</div>

{{-- Filters --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-5">
    <form method="GET" class="flex flex-wrap gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('messages.search') }}..."
               class="flex-1 min-w-48 border border-gray-300 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        <select name="status" class="border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">{{ __('client.status') }}</option>
            @foreach(['active','potential','vip','inactive'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ __('client.status_'.$s) }}</option>
            @endforeach
        </select>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-xl text-sm transition">
            <i class="fas fa-search me-1"></i>{{ __('messages.filter') }}
        </button>
        <a href="{{ route('clients.index') }}" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-xl text-sm transition">{{ __('messages.reset') }}</a>
    </form>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 border-b border-gray-100">
                <th class="text-start px-5 py-3 font-semibold text-gray-600">{{ __('client.client') }}</th>
                <th class="text-start px-4 py-3 font-semibold text-gray-600 hidden md:table-cell">{{ __('client.phone') }}</th>
                <th class="text-start px-4 py-3 font-semibold text-gray-600 hidden lg:table-cell">{{ __('client.city') }}</th>
                <th class="text-start px-4 py-3 font-semibold text-gray-600">{{ __('client.status') }}</th>
                <th class="text-start px-4 py-3 font-semibold text-gray-600 hidden xl:table-cell">{{ __('client.projects_count') }}</th>
                <th class="text-start px-4 py-3 font-semibold text-gray-600 hidden xl:table-cell">{{ __('client.lifetime_value') }}</th>
                <th class="text-end px-5 py-3 font-semibold text-gray-600">{{ __('messages.actions') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($clients as $client)
            <tr class="hover:bg-gray-50 transition">
                <td class="px-5 py-3">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full {{ $client->status === 'vip' ? 'bg-yellow-100' : 'bg-blue-100' }} flex items-center justify-center font-bold {{ $client->status === 'vip' ? 'text-yellow-600' : 'text-blue-600' }} flex-shrink-0">
                            {{ mb_substr($client->name, 0, 1) }}
                        </div>
                        <div>
                            <a href="{{ route('clients.show', $client) }}" class="font-medium text-gray-800 hover:text-blue-600">{{ $client->name }}</a>
                            @if($client->company_name)<p class="text-xs text-gray-400">{{ $client->company_name }}</p>@endif
                        </div>
                    </div>
                </td>
                <td class="px-4 py-3 hidden md:table-cell text-gray-600">{{ $client->phone ?? '-' }}</td>
                <td class="px-4 py-3 hidden lg:table-cell text-gray-600">{{ $client->city ?? '-' }}</td>
                <td class="px-4 py-3">
                    <span class="px-2 py-1 rounded-full text-xs font-medium
                        {{ $client->status === 'vip' ? 'bg-yellow-100 text-yellow-700' :
                           ($client->status === 'active' ? 'bg-green-100 text-green-700' :
                           ($client->status === 'inactive' ? 'bg-red-100 text-red-600' : 'bg-blue-100 text-blue-700')) }}">
                        {{ __('client.status_'.$client->status) }}
                    </span>
                </td>
                <td class="px-4 py-3 hidden xl:table-cell text-gray-600">{{ $client->projects->count() }}</td>
                <td class="px-4 py-3 hidden xl:table-cell font-medium text-gray-800">{{ number_format($client->lifetime_value) }}</td>
                <td class="px-5 py-3 text-end">
                    <div class="flex items-center justify-end gap-1">
                        <a href="{{ route('clients.show', $client) }}" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition"><i class="fas fa-eye"></i></a>
                        @can('update', $client)
                        <a href="{{ route('clients.edit', $client) }}" class="p-1.5 text-gray-600 hover:bg-gray-100 rounded-lg transition"><i class="fas fa-edit"></i></a>
                        @endcan
                        @can('delete', $client)
                        <form action="{{ route('clients.destroy', $client) }}" method="POST" onsubmit="return confirmDelete(this)">
                            @csrf @method('DELETE')
                            <button type="submit" class="p-1.5 text-red-400 hover:bg-red-50 rounded-lg transition"><i class="fas fa-trash"></i></button>
                        </form>
                        @endcan
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="py-16 text-center text-gray-400">
                <i class="fas fa-users text-5xl mb-3 block text-gray-200"></i>
                {{ __('client.no_clients') }}
            </td></tr>
            @endforelse
        </tbody>
    </table>
    @if($clients->hasPages())
    <div class="px-5 py-4 border-t">{{ $clients->links() }}</div>
    @endif
</div>
@endsection
