{{-- Cookie consent banner — inline styles so it works without a rebuild --}}
<style>
  .cc-wrap{position:fixed;inset-inline:0;bottom:0;z-index:9998;display:flex;justify-content:center;padding:16px;pointer-events:none}
  .cc-card{
    pointer-events:auto;width:100%;max-width:560px;border-radius:18px;padding:18px 20px;
    background:rgba(255,255,255,.97);border:1px solid #E2E8F0;
    box-shadow:0 24px 48px -20px rgba(15,23,42,.28), 0 4px 12px -6px rgba(15,23,42,.08);
    backdrop-filter:blur(10px);
    font-family:'Cairo',system-ui,-apple-system,"Segoe UI",sans-serif;
    transform:translateY(14px);opacity:0;transition:transform .28s ease,opacity .28s ease;
  }
  .cc-card.cc-in{transform:translateY(0);opacity:1}
  .cc-head{display:flex;align-items:flex-start;gap:12px}
  .cc-icon{
    flex:0 0 auto;width:38px;height:38px;border-radius:12px;display:flex;align-items:center;justify-content:center;
    background:linear-gradient(135deg,#EEF2FF,#F5F3FF);color:#4F46E5;
  }
  .cc-icon svg{width:20px;height:20px}
  .cc-title{font-size:14.5px;font-weight:800;color:#0F172A;margin:0 0 4px;letter-spacing:-.01em}
  .cc-text{font-size:12.5px;line-height:1.75;color:#64748B;margin:0}
  .cc-text a{color:#4F46E5;text-decoration:none;font-weight:600}
  .cc-text a:hover{text-decoration:underline}
  .cc-actions{display:flex;gap:8px;margin-top:16px;flex-wrap:wrap}
  .cc-btn{
    appearance:none;border:0;cursor:pointer;font-family:inherit;font-size:13px;font-weight:700;
    padding:10px 16px;border-radius:11px;transition:background .15s ease,transform .15s ease,border-color .15s ease;
    flex:1;min-width:120px;
  }
  .cc-btn:active{transform:translateY(1px)}
  .cc-accept{background:#4F46E5;color:#fff;box-shadow:0 10px 20px -10px rgba(79,70,229,.55)}
  .cc-accept:hover{background:#4338CA}
  .cc-essential{background:#F1F5F9;color:#334155;border:1px solid #E2E8F0}
  .cc-essential:hover{background:#E2E8F0}
  .cc-note{margin-top:10px;font-size:11px;color:#94A3B8}
  @media (prefers-color-scheme: dark){
    .cc-card{background:rgba(15,23,42,.97);border-color:#1E293B;box-shadow:0 24px 48px -20px rgba(0,0,0,.6)}
    .cc-title{color:#E2E8F0}
    .cc-text{color:#94A3B8}
    .cc-icon{background:linear-gradient(135deg,rgba(79,70,229,.18),rgba(124,58,237,.18));color:#A5B4FC}
    .cc-essential{background:#1E293B;color:#CBD5E1;border-color:#334155}
    .cc-essential:hover{background:#334155}
    .cc-note{color:#64748B}
  }
  @media (max-width:480px){
    .cc-card{padding:16px 16px 14px;border-radius:16px}
    .cc-btn{flex:1 1 100%}
  }
</style>

<div class="cc-wrap" id="ccWrap" hidden>
  <div class="cc-card" id="ccCard" role="dialog" aria-live="polite" aria-label="{{ __('messages.cookie_title') }}">
    <div class="cc-head">
      <div class="cc-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
          <path d="M12 2a10 10 0 1 0 10 10 4 4 0 0 1-5-5 4 4 0 0 1-5-5Z"/>
          <circle cx="9" cy="10" r="1"/><circle cx="14" cy="14" r="1"/><circle cx="8.5" cy="15.5" r="1"/>
        </svg>
      </div>
      <div>
        <p class="cc-title">{{ __('messages.cookie_title') }}</p>
        <p class="cc-text">{{ __('messages.cookie_message') }}</p>
      </div>
    </div>

    <div class="cc-actions">
      <button type="button" class="cc-btn cc-accept" id="ccAccept">{{ __('messages.cookie_accept') }}</button>
      <button type="button" class="cc-btn cc-essential" id="ccEssential">{{ __('messages.cookie_essential') }}</button>
    </div>
    <p class="cc-note">{{ __('messages.cookie_note') }}</p>
  </div>
</div>

<script>
  (function () {
    var KEY = 'iprojects_cookie_consent';
    var wrap = document.getElementById('ccWrap');
    var card = document.getElementById('ccCard');
    if (!wrap || !card) return;

    function stored() {
      try { return localStorage.getItem(KEY); } catch (e) { return null; }
    }

    function save(choice) {
      try {
        localStorage.setItem(KEY, JSON.stringify({ choice: choice, at: new Date().toISOString(), v: 1 }));
      } catch (e) {}
      hide();
    }

    function hide() {
      card.classList.remove('cc-in');
      setTimeout(function () { wrap.setAttribute('hidden', ''); }, 300);
    }

    function show() {
      wrap.removeAttribute('hidden');
      requestAnimationFrame(function () {
        requestAnimationFrame(function () { card.classList.add('cc-in'); });
      });
    }

    if (!stored()) {
      show();
    }

    var accept = document.getElementById('ccAccept');
    var essential = document.getElementById('ccEssential');
    if (accept) accept.addEventListener('click', function () { save('all'); });
    if (essential) essential.addEventListener('click', function () { save('essential'); });
  })();
</script>
