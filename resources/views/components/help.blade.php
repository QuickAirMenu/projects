@props(['text'])

@once
    @push('styles')
        <style>
            .ip-tip-wrap { position: relative; display: inline-flex; align-items: center; vertical-align: middle; }
            .ip-tip-icon { color: #9ca3af; font-size: .72rem; cursor: help; }
            .ip-tip-wrap:hover .ip-tip-icon, .ip-tip-wrap:focus-within .ip-tip-icon { color: #2563eb; }
            .ip-tip {
                position: absolute; bottom: 100%; inset-inline-start: 0; margin-bottom: 6px;
                z-index: 60; width: 16rem; max-width: 70vw;
                background: #111827; color: #fff; font-size: 11px; line-height: 1.6;
                padding: 8px 10px; border-radius: 8px; box-shadow: 0 8px 20px rgba(0, 0, 0, .18);
                opacity: 0; visibility: hidden; transition: opacity .15s ease;
                pointer-events: none; font-weight: 400; text-align: start;
            }
            .ip-tip::after {
                content: ""; position: absolute; top: 100%; inset-inline-start: 9px;
                border: 5px solid transparent; border-top-color: #111827;
            }
            .ip-tip-wrap:hover .ip-tip, .ip-tip-wrap:focus-within .ip-tip { opacity: 1; visibility: visible; }
        </style>
    @endpush
@endonce

<span class="ip-tip-wrap">
    <i class="fas fa-circle-question ip-tip-icon" tabindex="0" role="img" aria-label="{{ $text }}"></i>
    <span class="ip-tip" role="tooltip">{{ $text }}</span>
</span>
