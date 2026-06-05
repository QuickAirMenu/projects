@extends('layouts.app')
@section('title', __('finance.quotes'))

@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <h1 class="text-2xl font-bold text-gray-800">{{ __('finance.quotes') }}</h1>
    <a href="{{ route('quotes.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl text-sm transition font-medium">
        <i class="fas fa-plus me-2"></i>{{ __('finance.add_quote') }}
    </a>
</div>

@include('layouts.components.alert')

{{-- Stats --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    @foreach([
        ['label' => 'الإجمالي',  'val' => $stats['total'],   'color' => 'blue',  'icon' => 'fa-file-alt'],
        ['label' => 'مسودة',     'val' => $stats['draft'],   'color' => 'gray',  'icon' => 'fa-pencil-alt'],
        ['label' => 'معلّق',     'val' => $stats['pending'], 'color' => 'yellow','icon' => 'fa-clock'],
        ['label' => 'معتمد',     'val' => $stats['approved'],'color' => 'green', 'icon' => 'fa-check-circle'],
    ] as $c)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl bg-{{ $c['color'] }}-100 flex items-center justify-center">
            <i class="fas {{ $c['icon'] }} text-{{ $c['color'] }}-600 text-lg"></i>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-800">{{ $c['val'] }}</p>
            <p class="text-xs text-gray-500">{{ $c['label'] }}</p>
        </div>
    </div>
    @endforeach
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 border-b border-gray-100">
                <th class="text-start px-5 py-3 font-semibold text-gray-600">{{ __('finance.quote_number') }}</th>
                <th class="text-start px-4 py-3 font-semibold text-gray-600 hidden md:table-cell">{{ __('client.client') }}</th>
                <th class="text-start px-4 py-3 font-semibold text-gray-600">{{ __('messages.status') }}</th>
                <th class="text-start px-4 py-3 font-semibold text-gray-600 hidden lg:table-cell">{{ __('finance.total') }}</th>
                <th class="text-start px-4 py-3 font-semibold text-gray-600 hidden xl:table-cell">{{ __('finance.valid_until') }}</th>
                <th class="text-end px-5 py-3 font-semibold text-gray-600">{{ __('messages.actions') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($quotes as $quote)
            @php $sc = ['draft'=>'gray','sent'=>'blue','approved'=>'green','rejected'=>'red'][$quote->status] ?? 'gray' @endphp
            <tr class="hover:bg-gray-50 transition">
                <td class="px-5 py-3 font-medium text-gray-800">{{ $quote->quote_number }}</td>
                <td class="px-4 py-3 hidden md:table-cell text-gray-600">{{ $quote->client?->name }}</td>
                <td class="px-4 py-3">
                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-{{ $sc }}-100 text-{{ $sc }}-700">{{ ucfirst($quote->status) }}</span>
                </td>
                <td class="px-4 py-3 hidden lg:table-cell font-medium">{{ number_format($quote->total, 2) }}</td>
                <td class="px-4 py-3 hidden xl:table-cell text-gray-500">{{ $quote->valid_until?->format('Y-m-d') ?? '-' }}</td>
                <td class="px-5 py-3 text-end">
                    <div class="flex items-center justify-end gap-1">
                        <a href="{{ route('quotes.show', $quote) }}" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg"><i class="fas fa-eye"></i></a>
                        @if($quote->status === 'draft')
                        <a href="{{ route('quotes.edit', $quote) }}" class="p-1.5 text-gray-600 hover:bg-gray-100 rounded-lg"><i class="fas fa-edit"></i></a>
                        <form action="{{ route('quotes.send', $quote) }}" method="POST">
                            @csrf
                            <button type="submit" class="p-1.5 text-green-600 hover:bg-green-50 rounded-lg" title="إرسال"><i class="fas fa-paper-plane"></i></button>
                        </form>
                        @endif
                        <form action="{{ route('quotes.destroy', $quote) }}" method="POST" onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
                            @csrf @method('DELETE')
                            <button type="submit" class="p-1.5 text-red-400 hover:bg-red-50 rounded-lg"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="py-16 text-center text-gray-400">
                <i class="fas fa-file-alt text-5xl mb-3 block text-gray-200"></i>
                لا توجد عروض أسعار
            </td></tr>
            @endforelse
        </tbody>
    </table>
    @if($quotes->hasPages())
    <div class="px-5 py-4 border-t">{{ $quotes->links() }}</div>
    @endif
</div>
@endsection
