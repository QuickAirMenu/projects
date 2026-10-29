@extends('layouts.app')
@section('title', $client->name)

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'client.clients', 'url' => route('clients.index')],
        ['label' => $client->name, 'url' => null],
    ]" :show-home="false" />
@endsection

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ $client->name }}</h1>
        <p class="text-gray-500 text-sm mt-1">{{ $client->company_name }}</p>
    </div>
    <div class="flex gap-2">
        @if($client->email)
        <form action="{{ route('clients.portal-invite', $client) }}" method="POST">
            @csrf
            <button type="submit" class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-xl transition">
                <i class="fas fa-envelope"></i> {{ __('client.portal_invite') }}
            </button>
        </form>
        @endif
        @can('update', $client)
        <a href="{{ route('clients.edit', $client) }}" class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl transition">
            <i class="fas fa-edit"></i> {{ __('messages.edit') }}
        </a>
        @endcan
        <a href="{{ route('clients.index') }}" class="flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-xl transition">
            <i class="fas fa-arrow-right"></i> {{ __('messages.back') }}
        </a>
    </div>
</div>



<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Client Info Card --}}
    <div class="lg:col-span-1 space-y-4">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="flex flex-col items-center text-center mb-5">
                <div class="w-16 h-16 rounded-full bg-blue-100 flex items-center justify-center mb-3">
                    <span class="text-2xl font-bold text-blue-600">{{ mb_substr($client->name, 0, 1) }}</span>
                </div>
                <h2 class="font-bold text-gray-800 text-lg">{{ $client->name }}</h2>
                @if($client->company_name)<p class="text-gray-500 text-sm">{{ $client->company_name }}</p>@endif
                <span class="mt-2 px-3 py-1 rounded-full text-xs font-medium
                    {{ $client->status === 'vip' ? 'bg-yellow-100 text-yellow-700' :
                       ($client->status === 'active' ? 'bg-green-100 text-green-700' :
                       ($client->status === 'inactive' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700')) }}">
                    {{ __('client.status_'.$client->status) }}
                </span>
            </div>
            <div class="space-y-3 text-sm">
                @if($client->phone)
                <div class="flex items-center gap-3 text-gray-600 privacy-mask"><i class="fas fa-phone w-4 text-blue-400"></i> {{ $client->phone }}</div>
                @endif
                @if($client->whatsapp)
                <div class="flex items-center gap-3 text-gray-600 privacy-mask"><i class="fab fa-whatsapp w-4 text-green-400"></i> {{ $client->whatsapp }}</div>
                @endif
                @if($client->email)
                <div class="flex items-center gap-3 text-gray-600 privacy-mask"><i class="fas fa-envelope w-4 text-blue-400"></i> {{ $client->email }}</div>
                @endif
                @if($client->city)
                <div class="flex items-center gap-3 text-gray-600"><i class="fas fa-map-marker-alt w-4 text-red-400"></i> {{ $client->city }}</div>
                @endif
                @if($client->industry)
                <div class="flex items-center gap-3 text-gray-600"><i class="fas fa-industry w-4 text-gray-400"></i> {{ $client->industry }}</div>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between">
                <h3 class="font-semibold text-gray-800">{{ __('client.client_portal') }}</h3>
                <span class="text-xs px-2 py-1 rounded-full {{ $client->portalAccount?->email_verified_at ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                    {{ $client->portalAccount?->email_verified_at ? __('client.activated') : __('client.pending_setup') }}
                </span>
            </div>
            <p class="mt-3 text-sm text-gray-500">{{ $client->email ? __('client.portal_setup_message') : __('client.portal_setup_no_email') }}</p>
        </div>

        {{-- Stats --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="grid grid-cols-2 gap-4 text-center">
                <div>
                    <p class="text-2xl font-bold text-blue-600">{{ $client->projects->count() }}</p>
                    <p class="text-xs text-gray-500">{{ __('client.projects_count') }}</p>
                </div>
                <div>
                    <p class="text-2xl font-bold text-green-600 privacy-mask">{{ number_format($client->lifetime_value, 0) }}</p>
                    <p class="text-xs text-gray-500">{{ __('client.lifetime_value') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Right Column --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- Projects --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-800 flex items-center gap-2"><i class="fas fa-folder text-blue-500"></i> {{ __('project.projects') }}</h3>
                @can('create', App\Models\Project::class)
                <a href="{{ route('projects.create') }}?client_id={{ $client->id }}" class="text-sm text-blue-600 hover:underline">+ {{ __('project.add_project') }}</a>
                @endcan
            </div>
            @forelse($client->projects as $project)
            <div class="flex items-center justify-between py-3 border-b last:border-0">
                <div>
                    <a href="{{ route('projects.show', $project) }}" class="font-medium text-gray-800 hover:text-blue-600">{{ $project->name }}</a>
                    <p class="text-xs text-gray-400">{{ $project->deadline?->format('Y-m-d') }}</p>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-medium" style="background:{{ $project->status->color }}20; color:{{ $project->status->color }}">
                    {{ $project->status->name }}
                </span>
            </div>
            @empty
            <p class="text-gray-400 text-sm text-center py-4">{{ __('project.no_projects') }}</p>
            @endforelse
        </div>

        {{-- Communication Log --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-800 flex items-center gap-2"><i class="fas fa-comments text-purple-500"></i> {{ __('client.communications') }}</h3>
            </div>
            <form action="{{ route('clients.communications.store', $client) }}" method="POST" class="mb-4 p-4 bg-gray-50 rounded-xl">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
                    <select name="type" required class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="call">📞 {{ __('messages.call') }}</option>
                        <option value="email">📧 Email</option>
                        <option value="meeting">🤝 {{ __('messages.meeting') }}</option>
                        <option value="whatsapp">💬 WhatsApp</option>
                        <option value="other">{{ __('messages.other') }}</option>
                    </select>
                    <input type="datetime-local" name="communication_date" value="{{ now()->format('Y-m-d\TH:i') }}" required
                           class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <input type="date" name="follow_up_date" placeholder="{{ __('client.follow_up_date') }}"
                           class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <textarea name="message" required rows="2" placeholder="{{ __('messages.notes') }}..."
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 mb-2"></textarea>
                <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white text-sm px-4 py-2 rounded-lg transition">
                    <i class="fas fa-plus me-1"></i>{{ __('client.add_communication') }}
                </button>
            </form>

            @forelse($client->communications as $comm)
            <div class="flex gap-3 py-3 border-b last:border-0">
                <div class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-{{ $comm->type === 'call' ? 'phone' : ($comm->type === 'email' ? 'envelope' : ($comm->type === 'whatsapp' ? 'comment' : 'users')) }} text-purple-600 text-sm"></i>
                </div>
                <div class="flex-1">
                    <p class="text-sm text-gray-700">{{ $comm->message }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $comm->user->name }} · {{ \Carbon\Carbon::parse($comm->communication_date)->format('Y-m-d H:i') }}</p>
                </div>
            </div>
            @empty
            <p class="text-gray-400 text-sm text-center py-4">{{ __('client.no_communications') }}</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
