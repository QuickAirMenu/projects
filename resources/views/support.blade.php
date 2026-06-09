@extends('layouts.app')
@section('title', __('support.title'))

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">{{ __('support.title') }}</h1>
    <p class="text-gray-500 mt-1">{{ __('support.subtitle') }}</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    @php $cards = [
        ['icon' => 'fa-whatsapp', 'color' => 'green', 'title' => __('support.whatsapp'), 'desc' => __('support.whatsapp_desc'), 'link' => 'https://wa.me/966500000000', 'linkText' => __('support.chat_now')],
        ['icon' => 'fa-envelope', 'color' => 'blue', 'title' => __('support.email'), 'desc' => __('support.email_desc'), 'link' => 'mailto:support@airmenu.net', 'linkText' => 'support@airmenu.net'],
        ['icon' => 'fa-clock', 'color' => 'amber', 'title' => __('support.hours'), 'desc' => __('support.hours_desc'), 'link' => null, 'linkText' => null],
    ]; @endphp
    @foreach($cards as $card)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 text-center">
        <div class="w-14 h-14 mx-auto mb-4 rounded-xl bg-{{ $card['color'] }}-100 flex items-center justify-center">
            <i class="fab {{ $card['icon'] }} text-{{ $card['color'] }}-600 text-2xl"></i>
        </div>
        <h3 class="font-bold text-lg text-gray-800 mb-2">{{ $card['title'] }}</h3>
        <p class="text-gray-500 text-sm mb-4">{{ $card['desc'] }}</p>
        @if($card['link'])
        <a href="{{ $card['link'] }}" target="_blank" class="inline-block bg-{{ $card['color'] }}-600 hover:bg-{{ $card['color'] }}-700 text-white px-6 py-2.5 rounded-xl text-sm font-medium transition">
            {{ $card['linkText'] }}
        </a>
        @endif
    </div>
    @endforeach
</div>
@if(auth()->check())
<div class="mt-8 bg-white rounded-2xl shadow-sm border border-gray-100 p-6 max-w-2xl">
    <h2 class="font-bold text-lg text-gray-800 mb-4">{{ __('support.send_message') }}</h2>
    <form method="POST" action="{{ route('support.send') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('support.subject') }}</label>
            <input type="text" name="subject" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('support.message') }}</label>
            <textarea name="message" rows="4" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
        </div>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2.5 rounded-xl transition">
            <i class="fas fa-paper-plane me-2"></i>{{ __('support.send') }}
        </button>
    </form>
</div>
@endif
@endsection
