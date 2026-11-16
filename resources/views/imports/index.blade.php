@extends('layouts.app')

@section('title', __('messages.import_data'))

@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'messages.import_data', 'url' => route('imports.index')]]" :show-home="false" />
@endsection

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">{{ __('messages.import_data') }}</h1>
        <p class="mt-1 text-sm text-gray-500">{{ __('messages.import_hint') }}</p>
    </div>

    <div class="grid gap-6 md:grid-cols-2">
        {{-- Clients --}}
        <div class="card rounded-2xl p-5">
            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100">
                <i class="fas fa-users text-blue-600"></i>
            </div>
            <h2 class="font-semibold text-gray-900">{{ __('messages.import_clients') }}</h2>
            <p class="mt-1 mb-4 text-xs text-gray-500">{{ __('messages.import_clients_hint') }}</p>

            <form method="POST" action="{{ route('imports.clients') }}" enctype="multipart/form-data" class="space-y-3">
                @csrf
                <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                       class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm file:me-3 file:rounded-lg file:border-0 file:bg-gray-100 file:px-3 file:py-1.5 file:text-xs">
                <button type="submit" class="w-full rounded-xl bg-primary-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-primary-700">
                    <i class="fas fa-upload me-2"></i>{{ __('messages.import') }}
                </button>
            </form>

            <a href="{{ route('imports.clients.template') }}" class="mt-3 inline-flex items-center gap-2 text-xs font-medium text-primary-600 hover:underline">
                <i class="fas fa-file-arrow-down"></i>{{ __('messages.download_template') }}
            </a>
        </div>

        {{-- Projects --}}
        <div class="card rounded-2xl p-5">
            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100">
                <i class="fas fa-briefcase text-emerald-600"></i>
            </div>
            <h2 class="font-semibold text-gray-900">{{ __('messages.import_projects') }}</h2>
            <p class="mt-1 mb-4 text-xs text-gray-500">{{ __('messages.import_projects_hint') }}</p>

            <form method="POST" action="{{ route('imports.projects') }}" enctype="multipart/form-data" class="space-y-3">
                @csrf
                <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                       class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm file:me-3 file:rounded-lg file:border-0 file:bg-gray-100 file:px-3 file:py-1.5 file:text-xs">
                <button type="submit" class="w-full rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-emerald-700">
                    <i class="fas fa-upload me-2"></i>{{ __('messages.import') }}
                </button>
            </form>

            <a href="{{ route('imports.projects.template') }}" class="mt-3 inline-flex items-center gap-2 text-xs font-medium text-emerald-600 hover:underline">
                <i class="fas fa-file-arrow-down"></i>{{ __('messages.download_template') }}
            </a>
        </div>
    </div>

    <div class="card mt-6 rounded-2xl p-5">
        <h3 class="mb-2 font-semibold text-gray-900"><i class="fas fa-circle-info me-2 text-gray-400"></i>{{ __('messages.import_notes') }}</h3>
        <ul class="space-y-1 text-sm text-gray-600">
            <li>• {{ __('messages.import_note_format') }}</li>
            <li>• {{ __('messages.import_note_template') }}</li>
            <li>• {{ __('messages.import_note_duplicates') }}</li>
        </ul>
    </div>
@endsection
