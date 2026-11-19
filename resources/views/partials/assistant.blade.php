{{-- Sapa Madani (AI Asisten / Chatbot) + mobile bottom bar. WA configured → open WhatsApp, else the chat panel. --}}
@php
    $assistant = $assistant ?? assistant_settings();
    $waUrl = $assistant['wa_url'];
@endphp

@if($assistant['enabled'])
    {{-- Desktop floating button (mobile uses the bottom bar) --}}
    @if($waUrl)
        <a href="{{ $waUrl }}" target="_blank" rel="noopener" class="sm-fab d-none d-md-flex" aria-label="Sapa Madani via WhatsApp">
            <i class="fab fa-whatsapp" aria-hidden="true"></i><span>Sapa Madani</span>
        </a>
    @else
        <button type="button" class="sm-fab d-none d-md-flex" data-sm-open aria-controls="smPanel" aria-expanded="false">
            <i class="fas fa-robot" aria-hidden="true"></i><span>Sapa Madani</span>
        </button>

        <div class="sm-panel" id="smPanel" role="dialog" aria-label="Sapa Madani" hidden>
            <div class="sm-head">
                <div><i class="fas fa-robot me-2" aria-hidden="true"></i><strong>Sapa Madani</strong><small class="d-block">Asisten Virtual PPID Empat Lawang</small></div>
                <button type="button" class="sm-close" data-sm-close aria-label="Tutup">&times;</button>
            </div>
            <div class="sm-body" id="smMessages" aria-live="polite">
                <div class="sm-msg sm-bot">Halo! Saya Sapa Madani. Ada yang bisa saya bantu terkait informasi publik Kabupaten Empat Lawang?</div>
            </div>
            <form class="sm-form" id="smForm">
                <input type="text" id="smInput" class="form-control" maxlength="1000" placeholder="Tulis pertanyaan..." autocomplete="off" required>
                <button type="submit" class="btn btn-primary" aria-label="Kirim"><i class="fas fa-paper-plane" aria-hidden="true"></i></button>
            </form>
        </div>
    @endif
@endif

{{-- Mobile bottom bar --}}
<nav class="sm-bottombar d-md-none" aria-label="Navigasi bawah">
    <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}"><i class="fas fa-house" aria-hidden="true"></i><span>Beranda</span></a>
    <a href="{{ route('informasi-publik.index') }}" class="{{ request()->routeIs('informasi-publik.*') ? 'active' : '' }}"><i class="fas fa-folder-open" aria-hidden="true"></i><span>Informasi</span></a>
    @if($assistant['enabled'])
        @if($waUrl)
            <a href="{{ $waUrl }}" target="_blank" rel="noopener" class="sm-bb-main"><i class="fab fa-whatsapp" aria-hidden="true"></i><span>Sapa Madani</span></a>
        @else
            <button type="button" class="sm-bb-main" data-sm-open aria-controls="smPanel"><i class="fas fa-robot" aria-hidden="true"></i><span>Sapa Madani</span></button>
        @endif
    @endif
    <a href="{{ route('request.create') }}" class="{{ request()->routeIs('request.*') ? 'active' : '' }}"><i class="fas fa-file-signature" aria-hidden="true"></i><span>Permohonan</span></a>
    <button type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" onclick="window.scrollTo({top: 0})"><i class="fas fa-bars" aria-hidden="true"></i><span>Menu</span></button>
</nav>

<style>
    .sm-fab {
        position: fixed; right: 18px; bottom: 84px; z-index: 1079; align-items: center; gap: 8px;
        padding: 12px 18px; border: 0; border-radius: 999px; background: var(--primary-color, #0284c7);
        color: #fff; font-weight: 600; text-decoration: none; box-shadow: 0 6px 18px rgba(0,0,0,.25);
    }
    .sm-fab:hover, .sm-fab:focus-visible { background: var(--accent-color, #0369a1); color: #fff; }
    .sm-fab .fa-whatsapp { font-size: 1.3rem; }
    .sm-panel {
        position: fixed; right: 18px; bottom: 140px; z-index: 1085; width: 360px; max-width: calc(100vw - 24px);
        height: 480px; max-height: calc(100vh - 160px); display: flex; flex-direction: column;
        background: #fff; border-radius: 16px; box-shadow: 0 12px 40px rgba(0,0,0,.25); overflow: hidden;
    }
    .sm-panel[hidden] { display: none; }
    .sm-head { display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; background: var(--primary-color, #0284c7); color: #fff; }
    .sm-head small { opacity: .85; font-size: .75rem; }
    .sm-close { background: transparent; border: 0; color: #fff; font-size: 1.6rem; line-height: 1; }
    .sm-body { flex: 1; overflow-y: auto; padding: 14px; background: #f8fafc; display: flex; flex-direction: column; gap: 8px; }
    .sm-msg { max-width: 85%; padding: 9px 13px; border-radius: 14px; font-size: .9rem; white-space: pre-wrap; word-wrap: break-word; }
    .sm-bot { align-self: flex-start; background: #fff; border: 1px solid #e2e8f0; }
    .sm-user { align-self: flex-end; background: var(--primary-color, #0284c7); color: #fff; }
    .sm-form { display: flex; gap: 8px; padding: 10px; border-top: 1px solid #e2e8f0; }

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
        background: var(--primary-color, #0284c7); color: #fff; font-size: 1.35rem; box-shadow: 0 4px 12px rgba(2,132,199,.45);
    }
    @media (max-width: 767.98px) {
        body { padding-bottom: 64px; }
        .a11y-widget { bottom: 80px; }
        .a11y-panel { max-height: calc(100vh - 160px); max-height: calc(100dvh - 160px); }
        .sm-panel { left: 12px; right: 12px; width: auto; bottom: 76px; max-height: calc(100vh - 140px); }
    }
</style>

@if($assistant['enabled'] && ! $waUrl)
<script>
(function () {
    const panel = document.getElementById('smPanel');
    const box = document.getElementById('smMessages');
    const form = document.getElementById('smForm');
    const input = document.getElementById('smInput');
    const openers = document.querySelectorAll('[data-sm-open]');

    function toggle(show) {
        panel.hidden = !show;
        openers.forEach(b => b.setAttribute('aria-expanded', show));
        if (show) input.focus();
    }
    function add(text, who) {
        const el = document.createElement('div');
        el.className = 'sm-msg sm-' + who;
        el.textContent = text; // never innerHTML: replies come from an external API
        box.appendChild(el);
        box.scrollTop = box.scrollHeight;
        return el;
    }

    openers.forEach(b => b.addEventListener('click', () => toggle(panel.hidden)));
    panel.querySelector('[data-sm-close]').addEventListener('click', () => toggle(false));
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && !panel.hidden) toggle(false); });

    form.addEventListener('submit', async e => {
        e.preventDefault();
        const message = input.value.trim();
        if (!message) return;
        add(message, 'user');
        input.value = '';
        const pending = add('…', 'bot');
        try {
            const res = await fetch(@json(route('assistant.chat')), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json', 'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ message }),
            });
            const data = await res.json();
            pending.textContent = res.ok && data.reply ? data.reply : (res.status === 429 ? 'Terlalu banyak pesan, coba lagi sebentar.' : 'Maaf, terjadi kesalahan. Silakan coba lagi.');
        } catch (err) {
            pending.textContent = 'Koneksi gagal. Silakan coba lagi.';
        }
    });
})();
</script>
@endif
