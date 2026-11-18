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

        {{-- Quotes --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-800 flex items-center gap-2"><i class="fas fa-file-signature text-indigo-500"></i> {{ __('client.quotes_title') }}</h3>
                @can('create', App\Models\Quote::class)
                <a href="{{ route('quotes.create') }}?client_id={{ $client->id }}" class="text-sm text-blue-600 hover:underline">+ {{ __('finance.new_quote') }}</a>
                @endcan
            </div>
            @forelse($quotes as $quote)
            <div class="flex items-center justify-between py-2.5 border-b last:border-0 text-sm">
                <div>
                    <a href="{{ route('quotes.show', $quote) }}" class="font-medium text-gray-800 hover:text-blue-600">{{ $quote->quote_number }}</a>
                    <p class="text-xs text-gray-400">{{ $quote->valid_until?->format('Y-m-d') }}</p>
                </div>
                <div class="text-end">
                    <p class="font-medium text-gray-700">{{ number_format((float) $quote->total, 2) }}</p>
                    <span class="text-xs text-gray-500">{{ __('finance.quote_status.'.$quote->status) }}</span>
                </div>
            </div>
            @empty
            <p class="text-gray-400 text-sm text-center py-4">{{ __('client.no_quotes') }}</p>
            @endforelse
        </div>

        {{-- Invoices --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-semibold text-gray-800 flex items-center gap-2"><i class="fas fa-receipt text-emerald-500"></i> {{ __('client.invoices_title') }}</h3>
                @can('create', App\Models\Invoice::class)
                <a href="{{ route('invoices.create') }}?client_id={{ $client->id }}" class="text-sm text-blue-600 hover:underline">+ {{ __('finance.add_invoice') }}</a>
                @endcan
            </div>
            <div class="mb-3 grid grid-cols-3 gap-2 text-center text-xs">
                <div class="rounded-xl bg-gray-50 p-2"><p class="text-gray-500">{{ __('client.invoiced') }}</p><p class="mt-1 font-bold text-gray-800 privacy-mask">{{ number_format($totals['invoiced'], 2) }}</p></div>
                <div class="rounded-xl bg-emerald-50 p-2"><p class="text-emerald-600">{{ __('client.paid') }}</p><p class="mt-1 font-bold text-emerald-700 privacy-mask">{{ number_format($totals['paid'], 2) }}</p></div>
                <div class="rounded-xl bg-red-50 p-2"><p class="text-red-500">{{ __('client.outstanding') }}</p><p class="mt-1 font-bold text-red-600 privacy-mask">{{ number_format($totals['outstanding'], 2) }}</p></div>
            </div>
            @forelse($invoices as $invoice)
            <div class="flex items-center justify-between py-2.5 border-b last:border-0 text-sm">
                <div>
                    <a href="{{ route('invoices.show', $invoice) }}" class="font-medium text-gray-800 hover:text-blue-600">{{ $invoice->invoice_number }}</a>
                    <p class="text-xs text-gray-400">{{ $invoice->invoice_date?->format('Y-m-d') }}</p>
                </div>
                <div class="text-end">
                    <p class="font-medium text-gray-700">{{ number_format((float) $invoice->total, 2) }}</p>
                    <span class="text-xs text-gray-500">{{ __('finance.payment_status.'.$invoice->payment_status) }}</span>
                </div>
            </div>
            @empty
            <p class="text-gray-400 text-sm text-center py-4">{{ __('client.no_invoices') }}</p>
            @endforelse
        </div>

        {{-- Documents --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-800 flex items-center gap-2"><i class="fas fa-folder-open text-amber-500"></i> {{ __('client.documents') }}</h3>
                @can('update', $client)
                <button type="button" onclick="document.getElementById('docForm').classList.toggle('hidden')" class="text-sm text-blue-600 hover:underline"><i class="fas fa-plus me-1"></i>{{ __('client.add_document') }}</button>
                @endcan
            </div>

            @can('update', $client)
            <form id="docForm" action="{{ route('clients.documents.store', $client) }}" method="POST" enctype="multipart/form-data" class="hidden mb-4 rounded-xl bg-gray-50 p-4 space-y-3">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <select name="type" required class="rounded-xl border border-gray-300 px-3 py-2 text-sm">
                        @foreach(['commercial_registration','tax_certificate','national_id','contract','other'] as $type)
                        <option value="{{ $type }}">{{ __('client.doc_'.$type) }}</option>
                        @endforeach
                    </select>
                    <input name="title" required maxlength="150" placeholder="{{ __('client.document_title') }}" class="rounded-xl border border-gray-300 px-3 py-2 text-sm md:col-span-2">
                    <input name="number" maxlength="100" placeholder="{{ __('client.document_number') }}" class="rounded-xl border border-gray-300 px-3 py-2 text-sm">
                    <input type="date" name="issue_date" class="rounded-xl border border-gray-300 px-3 py-2 text-sm" title="{{ __('client.document_issue_date') }}">
                    <input type="date" name="expiry_date" class="rounded-xl border border-gray-300 px-3 py-2 text-sm" title="{{ __('client.document_expiry_date') }}">
                    <input type="file" name="file" required accept=".jpg,.jpeg,.png,.webp,.pdf" class="rounded-xl border border-gray-300 px-3 py-2 text-sm md:col-span-2">
                    <button class="rounded-xl bg-blue-600 px-4 py-2 text-sm text-white">{{ __('client.document_upload') }}</button>
                </div>
                @error('file')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
            </form>
            @endcan

            @forelse($client->documents as $document)
            <div class="flex items-start justify-between py-2.5 border-b last:border-0 text-sm gap-3">
                <div class="min-w-0">
                    <p class="font-medium text-gray-800 truncate">{{ $document->title }} <span class="text-xs text-gray-400">· {{ __('client.doc_'.$document->type) }}</span></p>
                    <p class="text-xs text-gray-400">
                        @if($document->number){{ $document->number }} · @endif
                        @if($document->expiry_date){{ __('client.document_expiry_date') }}: {{ $document->expiry_date->format('Y-m-d') }}@endif
                        @if($document->isExpired()) <span class="text-red-500">({{ __('client.expired') }})</span>@endif
                    </p>
                </div>
                <div class="flex items-center gap-3 shrink-0">
                    <a href="{{ route('clients.documents.download', [$client, $document]) }}" class="text-blue-600 text-xs"><i class="fas fa-download"></i></a>
                    @can('update', $client)
                    <form action="{{ route('clients.documents.destroy', [$client, $document]) }}" method="POST" onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
                        @csrf @method('DELETE')
                        <button class="text-red-500 text-xs"><i class="fas fa-trash"></i></button>
                    </form>
                    @endcan
                </div>
            </div>
            @empty
            <p class="text-gray-400 text-sm text-center py-4">{{ __('client.no_documents') }}</p>
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
                        <option value="call"><i class="fas fa-phone"></i> {{ __('messages.call') }}</option>
                        <option value="email"><i class="fas fa-envelope"></i> Email</option>
                        <option value="meeting"><i class="fas fa-handshake"></i> {{ __('messages.meeting') }}</option>
                        <option value="whatsapp"><i class="fas fa-comment"></i> WhatsApp</option>
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
