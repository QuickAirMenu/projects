@extends('layouts.app')
@section('title', __('support.title'))

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">{{ __('support.title') }}</h1>
    <p class="text-gray-500 mt-1">{{ __('support.subtitle') }}</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    @php $whatsapp = __('support.whatsapp'); $whatsappDesc = __('support.whatsapp_desc'); $chatNow = __('support.chat_now'); $email = __('support.email'); $emailDesc = __('support.email_desc'); $hours = __('support.hours'); $hoursDesc = __('support.hours_desc'); @endphp
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 text-center">
        <div class="w-14 h-14 mx-auto mb-4 rounded-xl bg-green-100 flex items-center justify-center">
            <i class="fab fa-whatsapp text-green-600 text-2xl"></i>
        </div>
        <h3 class="font-bold text-lg text-gray-800 mb-2">{{ $whatsapp }}</h3>
        <p class="text-gray-500 text-sm mb-4">{{ $whatsappDesc }}</p>
        <a href="https://wa.me/966500518950" target="_blank" class="inline-block bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-xl text-sm font-medium transition">{{ $chatNow }}</a>
    </div>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 text-center">
        <div class="w-14 h-14 mx-auto mb-4 rounded-xl bg-blue-100 flex items-center justify-center">
            <i class="fas fa-envelope text-blue-600 text-2xl"></i>
        </div>
        <h3 class="font-bold text-lg text-gray-800 mb-2">{{ $email }}</h3>
        <p class="text-gray-500 text-sm mb-4">{{ $emailDesc }}</p>
        <a href="mailto:support@airmenu.net" target="_blank" class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-xl text-sm font-medium transition">support@airmenu.net</a>
    </div>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 text-center">
        <div class="w-14 h-14 mx-auto mb-4 rounded-xl bg-amber-100 flex items-center justify-center">
            <i class="fas fa-clock text-amber-600 text-2xl"></i>
        </div>
        <h3 class="font-bold text-lg text-gray-800 mb-2">{{ $hours }}</h3>
        <p class="text-gray-500 text-sm mb-4">{{ $hoursDesc }}</p>
    </div>
</div>
@if(auth()->check())
<div class="mt-8 bg-white rounded-2xl shadow-sm border border-gray-100 p-6 max-w-2xl">
    <h2 class="font-bold text-lg text-gray-800 mb-4">{{ __('support.send_message') }}</h2>
    <form method="POST" action="{{ route('support.send') }}" enctype="multipart/form-data" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('support.subject') }}</label>
            <input type="text" name="subject" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('support.message') }}</label>
            <textarea name="message" rows="4" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('support.attachment') }}</label>
            <input type="file" name="attachment" accept="image/*,.pdf,.doc,.docx" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 file:me-2 file:rounded-lg file:border-0 file:bg-blue-50 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-blue-700 hover:file:bg-blue-100">
            <p class="text-xs text-gray-400 mt-1">{{ __('support.attachment_hint') }}</p>
        </div>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2.5 rounded-xl transition">
            <i class="fas fa-paper-plane me-2"></i>{{ __('support.send') }}
        </button>
    </form>
</div>
@endif
@endsection
