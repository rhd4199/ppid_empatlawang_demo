{{-- Sapa Madani (AI Asisten / Chatbot) + mobile bottom bar. WA configured → open WhatsApp, else the chat panel. --}}
@php
    $assistant = $assistant ?? assistant_settings();
    $waUrl = $assistant['wa_url'];
@endphp

@if($assistant['enabled'])
    {{-- Desktop floating button (mobile uses the bottom bar) --}}
    @if($waUrl)
        <a href="{{ $waUrl }}" target="_blank" rel="noopener" class="sm-fab d-none d-md-flex" aria-label="Sapa Madani via WhatsApp">
            <span class="sm-avatar"><i class="fab fa-whatsapp" aria-hidden="true"></i><b class="sm-ai" aria-hidden="true">AI</b></span>
            <span class="sm-label"><strong>Sapa Madani</strong><small>Tanya Asisten AI</small></span>
        </a>
    @else
        <button type="button" class="sm-fab d-none d-md-flex" data-sm-open aria-controls="smPanel" aria-expanded="false" aria-label="Tanya Asisten AI Sapa Madani">
            <span class="sm-avatar sm-avatar-logo"><img src="{{ asset('assets/images/sapa-madani.png') }}" alt="" class="sm-logo"><b class="sm-ai" aria-hidden="true">AI</b></span>
            <span class="sm-label"><strong>Sapa Madani</strong><small>Tanya Asisten AI</small></span>
        </button>

        @include('partials.assistant-panel')
    @endif

    {{-- Teaser bubble: shown once per session a few seconds after load --}}
    <div class="sm-teaser" id="smTeaser" role="status" hidden>
        <button type="button" class="sm-teaser-open"><strong>Halo!</strong> Ada yang bisa dibantu? Tanya Sapa Madani, asisten AI PPID.</button>
        <button type="button" class="sm-teaser-close" aria-label="Tutup pesan">&times;</button>
    </div>
@endif

{{-- Mobile bottom bar --}}
<nav class="sm-bottombar d-md-none" aria-label="Navigasi bawah">
    <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}"><i class="fas fa-house" aria-hidden="true"></i><span>Beranda</span></a>
    <a href="{{ route('informasi-publik.index') }}" class="{{ request()->routeIs('informasi-publik.*') ? 'active' : '' }}"><i class="fas fa-folder-open" aria-hidden="true"></i><span>Informasi</span></a>
    @if($assistant['enabled'])
        @if($waUrl)
            <a href="{{ $waUrl }}" target="_blank" rel="noopener" class="sm-bb-main" aria-label="Sapa Madani via WhatsApp"><i class="fab fa-whatsapp" aria-hidden="true"></i><span>Sapa Madani</span></a>
        @else
            <button type="button" class="sm-bb-main" data-sm-open aria-controls="smPanel" aria-label="Tanya Asisten AI Sapa Madani"><i class="sm-bb-logo" aria-hidden="true"><img src="{{ asset('assets/images/sapa-madani.png') }}" alt="" class="sm-logo"></i><span>Sapa Madani</span></button>
        @endif
    @endif
    <a href="{{ route('request.create') }}" class="{{ request()->routeIs('request.*') ? 'active' : '' }}"><i class="fas fa-file-signature" aria-hidden="true"></i><span>Permohonan</span></a>
    <button type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" onclick="window.scrollTo({top: 0})"><i class="fas fa-bars" aria-hidden="true"></i><span>Menu</span></button>
</nav>

<style>
    .sm-fab {
        position: fixed; right: 18px; bottom: 84px; z-index: 1079; align-items: center; gap: 10px;
        padding: 6px 18px 6px 6px; border: 0; border-radius: 999px; color: #fff; text-decoration: none; text-align: left;
        background: linear-gradient(135deg, var(--primary-color, #0284c7), var(--accent-color, #0369a1));
        box-shadow: 0 8px 22px rgba(2,132,199,.35), inset 0 1px 0 rgba(255,255,255,.25);
        transition: transform .2s ease, box-shadow .2s ease;
    }
    .sm-fab:hover, .sm-fab:focus-visible { color: #fff; transform: translateY(-3px); box-shadow: 0 12px 28px rgba(2,132,199,.45), inset 0 1px 0 rgba(255,255,255,.25); }
    .sm-avatar {
        position: relative; width: 42px; height: 42px; flex: none; border-radius: 50%; display: flex; align-items: center; justify-content: center;
        background: rgba(255,255,255,.18); border: 1px solid rgba(255,255,255,.35); font-size: 1.2rem; animation: sm-glow 3s ease-out infinite;
    }
    .sm-avatar .fa-whatsapp { font-size: 1.4rem; }
    /* Sapa Madani logo (public/assets/images/sapa-madani.png, white background) fills its round avatar; also used in the chat panel */
    .sm-logo { display: block; width: 100%; height: 100%; border-radius: 50%; object-fit: cover; background: #fff; }
    .sm-avatar-logo { background: #fff; padding: 2px; }
    .sm-ai {
        position: absolute; top: -4px; right: -6px; padding: 0 5px; border-radius: 999px; font-size: .6rem; font-weight: 800; line-height: 1.5;
        background: #facc15; color: #1e293b; box-shadow: 0 1px 4px rgba(0,0,0,.25);
    }
    .sm-label { display: flex; flex-direction: column; line-height: 1.15; }
    .sm-label strong { font-size: .95rem; }
    .sm-label small { font-size: .72rem; opacity: .9; }
    @keyframes sm-glow {
        0% { box-shadow: 0 0 0 0 rgba(255,255,255,.55); }
        60%, 100% { box-shadow: 0 0 0 12px rgba(255,255,255,0); }
    }
    @keyframes sm-glow-main {
        0% { box-shadow: 0 4px 12px rgba(2,132,199,.45), 0 0 0 0 rgba(2,132,199,.45); }
        60%, 100% { box-shadow: 0 4px 12px rgba(2,132,199,.45), 0 0 0 12px rgba(2,132,199,0); }
    }
    .sm-teaser {
        position: fixed; right: 18px; bottom: 150px; z-index: 1079; max-width: 260px; display: flex; align-items: flex-start; gap: 4px;
        padding: 10px 8px 10px 14px; border-radius: 14px 14px 4px 14px; background: #fff; color: #1e293b;
        border: 1px solid #e2e8f0; box-shadow: 0 10px 28px rgba(15,23,42,.18); font-size: .85rem; animation: sm-pop .35s ease-out;
    }
    .sm-teaser[hidden] { display: none; }
    .sm-teaser-open { background: none; border: 0; padding: 0; color: inherit; text-align: left; }
    .sm-teaser-close { background: none; border: 0; padding: 0 4px; color: #94a3b8; font-size: 1.2rem; line-height: 1; }
    @keyframes sm-pop { from { opacity: 0; transform: translateY(8px) scale(.96); } to { opacity: 1; transform: none; } }

    .sm-bottombar {
        position: fixed; left: 0; right: 0; bottom: 0; z-index: 1078; display: flex; justify-content: space-around; align-items: flex-end;
        height: 64px; padding-bottom: env(safe-area-inset-bottom); background: #fff; border-top: 1px solid #e2e8f0; box-shadow: 0 -4px 14px rgba(0,0,0,.08);
    }
    .sm-bottombar > a, .sm-bottombar > button {
        flex: 1; display: flex; flex-direction: column; align-items: center; gap: 2px; padding: 8px 0;
        background: none; border: 0; color: #64748b; font-size: .7rem; text-decoration: none;
    }
    .sm-bottombar i { font-size: 1.15rem; }
    .sm-bottombar .active { color: var(--primary-color, #0284c7); font-weight: 600; }
    .sm-bottombar .sm-bb-main { color: var(--primary-color, #0284c7); font-weight: 600; }
    .sm-bottombar .sm-bb-main i {
        width: 48px; height: 48px; margin-top: -26px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
        position: relative; background: linear-gradient(135deg, var(--primary-color, #0284c7), var(--accent-color, #0369a1));
        color: #fff; font-size: 1.35rem; border: 3px solid #fff; animation: sm-glow-main 3s ease-out infinite;
    }
    .sm-bottombar .sm-bb-main i::after {
        content: "AI"; position: absolute; top: -5px; right: -8px; padding: 0 4px; border-radius: 999px;
        font: 800 .55rem/1.5 system-ui, sans-serif; background: #facc15; color: #1e293b;
    }
    .sm-bottombar .sm-bb-main i.fa-whatsapp::after { content: none; }
    .sm-bottombar .sm-bb-main i.sm-bb-logo { background: #fff; padding: 2px; }
    @media (max-width: 767.98px) {
        body { padding-bottom: 64px; }
        .a11y-widget { bottom: 80px; }
        .a11y-panel { max-height: calc(100vh - 160px); max-height: calc(100dvh - 160px); }
        .sm-teaser {
            right: auto; left: 50%; bottom: 84px; width: max-content; max-width: calc(100vw - 160px);
            transform: translateX(-50%); border-radius: 14px; animation: none;
        }
    }
    @media (prefers-reduced-motion: reduce) {
        .sm-fab, .sm-avatar, .sm-teaser, .sm-bottombar .sm-bb-main i { animation: none !important; transition: none !important; }
        .sm-fab:hover, .sm-fab:focus-visible { transform: none; }
    }
</style>

@if($assistant['enabled'])
<script>
(function () {
    const teaser = document.getElementById('smTeaser');
    let seen = true;
    try { seen = sessionStorage.getItem('smTeaserShown') === '1'; sessionStorage.setItem('smTeaserShown', '1'); } catch (e) { /* storage blocked: skip teaser */ }
    if (seen) return;
    const mains = document.querySelectorAll('.sm-fab, .sm-bb-main');
    const hide = () => { teaser.hidden = true; };
    setTimeout(() => { teaser.hidden = false; setTimeout(hide, 8000); }, 3000);
    teaser.querySelector('.sm-teaser-close').addEventListener('click', hide);
    teaser.querySelector('.sm-teaser-open').addEventListener('click', () => {
        hide();
        const main = Array.from(mains).find(el => el.getClientRects().length); // visible one: desktop fab or mobile bar
        if (main) main.click();
    });
    mains.forEach(el => el.addEventListener('click', hide));
})();
</script>
@endif
