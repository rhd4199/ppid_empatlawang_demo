{{-- Sapa Madani chat panel. Opened by any [data-sm-open] button.
     $embed = true: full-frame panel for /sapa-madani/embed (other sites' iframe): always open, close asks the parent page,
     links open in a new tab. --}}
@php($embed = $embed ?? false)
<div class="sm-panel" id="smPanel" role="dialog" aria-labelledby="smTitle" hidden>
    <div class="sm-head">
        <span class="sm-head-avatar" aria-hidden="true"><img src="{{ asset('assets/images/sapa-madani.png') }}" alt="" class="sm-logo"><span class="sm-online-dot"></span></span>
        <div class="sm-head-text">
            <strong id="smTitle">Sapa Madani</strong>
            <small><span class="sm-online">Online</span> · Asisten AI PPID Empat Lawang</small>
        </div>
        <button type="button" class="sm-close sm-reset" data-sm-reset aria-label="Mulai percakapan baru" title="Mulai percakapan baru" hidden><i class="fas fa-rotate-left" aria-hidden="true"></i></button>
        <button type="button" class="sm-close" data-sm-close aria-label="Tutup">&times;</button>
    </div>
    <div class="sm-body" id="smMessages" aria-live="polite">
        <div class="sm-row sm-row-bot">
            <span class="sm-mini" aria-hidden="true"><img src="{{ asset('assets/images/sapa-madani.png') }}" alt="" class="sm-logo"></span>
            <div class="sm-msg sm-bot">Halo! Saya Sapa Madani. Ada yang bisa saya bantu terkait informasi publik Kabupaten Empat Lawang?</div>
        </div>
        <div class="sm-suggest" id="smSuggest" role="group" aria-label="Saran pertanyaan">
            @foreach(['Cara mengajukan permohonan informasi', 'Daftar informasi berkala', 'Cara mengajukan keberatan', 'Jam layanan PPID'] as $question)
                <button type="button" class="sm-chip">{{ $question }}</button>
            @endforeach
        </div>
    </div>
    <form class="sm-form" id="smForm">
        <input type="text" id="smInput" maxlength="1000" placeholder="Tulis pertanyaan..." autocomplete="off" aria-label="Pertanyaan" required>
        <button type="submit" class="sm-send" aria-label="Kirim" disabled><i class="fas fa-paper-plane" aria-hidden="true"></i></button>
    </form>
    @if($waUrl ?? null)
        <a href="{{ $waUrl }}" target="_blank" rel="noopener" class="sm-wa"><i class="fab fa-whatsapp" aria-hidden="true"></i> Lebih nyaman lewat WhatsApp? Chat Sapa Madani di WA</a>
    @endif
    <p class="sm-note">Jawaban AI bisa keliru — untuk permohonan resmi gunakan menu <a href="{{ route('request.create') }}" @if($embed) target="_blank" rel="noopener" @endif>Permohonan</a>.</p>
</div>

<style>
    .sm-panel {
        position: fixed; right: 18px; bottom: 150px; z-index: 1085; width: 400px; max-width: calc(100vw - 24px);
        height: 680px; max-height: calc(100vh - 170px); display: flex; flex-direction: column;
        background: #fff; border-radius: 20px; border: 1px solid #e2e8f0; overflow: hidden;
        box-shadow: 0 24px 60px rgba(15,23,42,.22), 0 4px 14px rgba(15,23,42,.08);
        transform-origin: bottom right; opacity: 0; transform: translateY(12px) scale(.96);
        transition: opacity .2s ease, transform .2s ease;
    }
    .sm-panel.is-open { opacity: 1; transform: none; }
    .sm-panel[hidden] { display: none; }
    .sm-head {
        display: flex; align-items: center; gap: 12px; padding: 14px 14px 14px 16px; color: #fff;
        background: linear-gradient(135deg, #0369a1, #075985);
    }
    .sm-head-avatar {
        position: relative; width: 42px; height: 42px; flex: none; border-radius: 50%; display: flex; align-items: center; justify-content: center;
        background: #fff; border: 2px solid rgba(255,255,255,.6); padding: 1px;
    }
    .sm-online-dot { position: absolute; right: 0; bottom: 1px; width: 11px; height: 11px; border-radius: 50%; background: #22c55e; border: 2px solid #075985; }
    .sm-head-text { flex: 1; min-width: 0; line-height: 1.25; }
    .sm-head-text strong { display: block; font-size: 1rem; }
    .sm-head-text small { display: block; font-size: .75rem; color: #e0f2fe; }
    .sm-online { color: #bbf7d0; font-weight: 600; }
    .sm-close {
        width: 34px; height: 34px; flex: none; border-radius: 50%; border: 0; background: rgba(255,255,255,.12);
        color: #fff; font-size: 1.4rem; line-height: 1; transition: background .15s;
    }
    .sm-close:hover, .sm-close:focus-visible { background: rgba(255,255,255,.28); }
    .sm-reset { font-size: .85rem; }
    .sm-reset[hidden] { display: none; }
    .sm-body {
        flex: 1; overflow-y: auto; padding: 16px 14px; background: #f8fafc; display: flex; flex-direction: column; gap: 10px;
        scrollbar-width: thin; scrollbar-color: #cbd5e1 transparent; overscroll-behavior: contain;
    }
    .sm-body::-webkit-scrollbar { width: 6px; }
    .sm-body::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
    .sm-row { display: flex; align-items: flex-end; gap: 8px; max-width: 88%; animation: sm-msg-in .25s ease-out; }
    .sm-row-bot { align-self: flex-start; }
    .sm-row-user { align-self: flex-end; }
    .sm-mini {
        width: 26px; height: 26px; flex: none; border-radius: 50%; display: flex; align-items: center; justify-content: center;
        background: #fff; border: 1px solid #e2e8f0;
    }
    .sm-msg { min-width: 0; padding: 9px 13px; font-size: .9rem; line-height: 1.5; white-space: pre-wrap; overflow-wrap: anywhere; }
    .sm-bot { background: #fff; color: #1e293b; border: 1px solid #e2e8f0; border-radius: 16px 16px 16px 4px; box-shadow: 0 1px 2px rgba(15,23,42,.05); }
    .sm-user { background: linear-gradient(135deg, #0369a1, #075985); color: #fff; border-radius: 16px 16px 4px 16px; }
    .sm-typing { display: flex; gap: 4px; padding: 13px 14px; }
    .sm-typing span { width: 6px; height: 6px; border-radius: 50%; background: #64748b; animation: sm-dot 1s infinite ease-in-out; }
    .sm-typing span:nth-child(2) { animation-delay: .15s; }
    .sm-typing span:nth-child(3) { animation-delay: .3s; }
    @keyframes sm-msg-in { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: none; } }
    @keyframes sm-dot { 0%, 80%, 100% { opacity: .3; transform: none; } 40% { opacity: 1; transform: translateY(-3px); } }
    .sm-form { display: flex; gap: 8px; padding: 10px 12px 6px; background: #fff; border-top: 1px solid #e2e8f0; }
    .sm-form input {
        flex: 1; min-width: 0; height: 42px; padding: 0 16px; border: 1px solid #cbd5e1; border-radius: 999px; background: #f8fafc;
        color: #1e293b; font-size: .9rem; outline: none; transition: border-color .15s, box-shadow .15s;
    }
    .sm-form input:focus { border-color: #0369a1; background: #fff; box-shadow: 0 0 0 3px rgba(3,105,161,.15); }
    .sm-form input:read-only { background: #f1f5f9; color: #64748b; cursor: progress; }
    .sm-send {
        width: 42px; height: 42px; flex: none; border: 0; border-radius: 50%; color: #fff;
        background: linear-gradient(135deg, #0369a1, #075985); transition: transform .15s, opacity .15s;
    }
    .sm-send:not(:disabled):hover { transform: scale(1.06); }
    .sm-send:disabled { background: #cbd5e1; color: #fff; cursor: not-allowed; }
    .sm-wa { display: block; padding: 8px 14px 4px; background: #fff; color: #128c7e; font-size: .78rem; font-weight: 600; text-align: center; text-decoration: none; }
    .sm-wa:hover { color: #075e54; text-decoration: underline; }
    .sm-note { margin: 0; padding: 0 14px 10px; background: #fff; color: #64748b; font-size: .7rem; line-height: 1.35; text-align: center; }
    .sm-suggest { display: flex; flex-wrap: wrap; gap: 6px; padding-left: 34px; animation: sm-msg-in .25s ease-out; }
    .sm-chip {
        padding: 6px 12px; border: 1px solid #bae6fd; border-radius: 999px; background: #f0f9ff; color: #075985;
        font-size: .8rem; line-height: 1.3; text-align: left; transition: background .15s, border-color .15s;
    }
    .sm-chip:hover, .sm-chip:focus-visible { background: #e0f2fe; border-color: #0369a1; }
    .sm-note a, .sm-bot a { color: #0369a1; font-weight: 600; }
    .sm-bot a { text-decoration: underline; text-underline-offset: 2px; }
    @media (max-width: 767.98px) {
        /* phones: full screen like WhatsApp; JS (fit) shrinks it to the visual viewport so the input sits right above the keyboard */
        .sm-panel {
            top: 0; left: 0; right: 0; bottom: auto; width: 100%; max-width: none; height: 100vh; height: 100dvh; max-height: none;
            border: 0; border-radius: 0; box-shadow: none; transform: translateY(16px); transform-origin: bottom center;
        }
        .sm-panel.is-open { transform: none; }
        .sm-head { padding-top: calc(14px + env(safe-area-inset-top)); }
        .sm-note { padding-bottom: calc(8px + env(safe-area-inset-bottom)); }
        .sm-form input { font-size: 16px; } /* < 16px makes iOS zoom in on focus */
        html.sm-lock, html.sm-lock body { overflow: hidden; } /* page behind must not scroll */
    }
    @media (prefers-reduced-motion: reduce) {
        .sm-panel, .sm-row, .sm-suggest, .sm-chip, .sm-typing span, .sm-send, .sm-close { animation: none !important; transition: none !important; }
    }
</style>

<script>
(function () {
    const panel = document.getElementById('smPanel');
    const box = document.getElementById('smMessages');
    const form = document.getElementById('smForm');
    const input = document.getElementById('smInput');
    const send = form.querySelector('.sm-send');
    const reset = panel.querySelector('[data-sm-reset]');
    const intro = box.innerHTML; // greeting + suggestion chips, restored on "new chat"
    const KEY = 'smChat', MAX_MESSAGES = 60, MAX_HISTORY = 10;
    const EMBED = @json($embed);
    const closePanel = () => EMBED ? parent.postMessage('sapa-madani:close', '*') : toggle(false); // embed: the parent hides the iframe
    let busy = false;
    // chat survives page changes within this tab (sessionStorage); storage blocked → page-only memory
    const blank = () => ({ messages: [], history: [], open: false });
    let state = blank();
    try { state = Object.assign(blank(), JSON.parse(sessionStorage.getItem(KEY))); } catch (e) { /* blocked or corrupt */ }
    if (!Array.isArray(state.messages) || !Array.isArray(state.history)) state = blank();
    let shown = 0; // stored messages rendered so far, counted from the newest; older ones load when scrolled to the top
    function save() {
        state.messages = state.messages.slice(-MAX_MESSAGES);
        state.history = state.history.slice(-MAX_HISTORY);
        try { sessionStorage.setItem(KEY, JSON.stringify(state)); } catch (e) { /* storage blocked: page-only memory */ }
    }

    function toggle(show, focus = true) {
        // queried at click time: the mobile bottom-bar opener is rendered after this script
        document.querySelectorAll('[data-sm-open]').forEach(b => b.setAttribute('aria-expanded', show));
        if (show) {
            panel.hidden = false;
            requestAnimationFrame(() => {
                panel.classList.add('is-open'); fit();
                box.scrollTop = box.scrollHeight; // always open at the newest message (only scrollable once visible)
                fillUp();
            });
            if (focus) input.focus();
        } else {
            panel.classList.remove('is-open');
            fit();
            setTimeout(() => { if (!panel.classList.contains('is-open')) panel.hidden = true; }, 200); // after the fade-out
        }
        state.open = show; save();
    }
    const isOpen = () => panel.classList.contains('is-open');
    // Phones: the keyboard shrinks only the visual viewport (Android Chrome, iOS Safari), so size the full-screen
    // panel to it — header on top, input right above the keyboard. Desktop keeps the CSS floating panel.
    const phone = window.matchMedia('(max-width: 767.98px)');
    const vv = window.visualViewport;
    function fit() {
        const on = phone.matches && isOpen();
        document.documentElement.classList.toggle('sm-lock', on);
        if (!on || !vv) { panel.style.height = panel.style.top = ''; return; }
        panel.style.height = vv.height + 'px';
        panel.style.top = vv.offsetTop + 'px';
        box.scrollTop = box.scrollHeight;
    }
    vv?.addEventListener('resize', fit);
    vv?.addEventListener('scroll', fit);
    phone.addEventListener?.('change', fit);
    const sync = () => { input.readOnly = busy; send.disabled = busy || !input.value.trim(); }; // readOnly keeps focus, unlike disabled
    // text nodes + <a> built with createElement, never innerHTML: replies come from an external API.
    // Only http(s) URLs (or bare www.…, as https) become links; other sites open in a new tab without referrer.
    function fill(el, text) {
        el.textContent = '';
        let last = 0;
        for (const m of text.matchAll(/(?:https?:\/\/|\bwww\.)[^\s<>"'()]+/g)) {
            const url = m[0].replace(/[.,;:!?]+$/, ''); // sentence punctuation after the URL
            let href;
            try { href = new URL(url.startsWith('www.') ? 'https://' + url : url); } catch (e) { continue; }
            el.append(text.slice(last, m.index));
            const a = document.createElement('a');
            a.href = href.href;
            // own-site links show only the path ("/informasi-publik/27"): the full preview/production host wastes 2–3 lines
            a.textContent = href.origin === location.origin ? href.pathname + href.search + href.hash : url;
            if (href.origin !== location.origin) { a.target = '_blank'; a.rel = 'noopener noreferrer nofollow'; }
            else if (EMBED) { a.target = '_blank'; a.rel = 'noopener'; } // PPID pages open beside the host site, not inside the frame
            el.append(a);
            last = m.index + url.length;
        }
        el.append(text.slice(last));
    }
    // before = row to insert above (lazy-loaded older messages); default appends and scrolls to the newest
    function add(text, who, before = null) {
        const row = document.createElement('div');
        row.className = 'sm-row sm-row-' + who;
        row.dataset.m = '';
        if (who === 'bot') row.innerHTML = '<span class="sm-mini" aria-hidden="true"><img src="' + @json(asset('assets/images/sapa-madani.png')) + '" alt="" class="sm-logo"></span>'; // static markup only (server-side URL)
        const el = document.createElement('div');
        el.className = 'sm-msg sm-' + who;
        if (who === 'bot') fill(el, text); else el.textContent = text;
        row.appendChild(el);
        box.insertBefore(row, before);
        if (!before) box.scrollTop = box.scrollHeight;
        return el;
    }
    function remember(text, who) { state.messages.push({ who, text }); shown++; reset.hidden = false; save(); shown = Math.min(shown, state.messages.length); }

    // Render the next PAGE older stored messages above the current ones, keeping the visible message in place.
    const PAGE = 10;
    function loadOlder() {
        const end = state.messages.length - shown;
        if (end <= 0) return false;
        const start = Math.max(0, end - PAGE), first = box.querySelector('[data-m]'), height = box.scrollHeight;
        state.messages.slice(start, end).forEach(m => { if (m && (m.who === 'user' || m.who === 'bot')) add(String(m.text), m.who, first); });
        shown += end - start;
        box.scrollTop += box.scrollHeight - height;
        return true;
    }
    // too few rows to scroll → the scroll event never fires, so load until the list overflows or nothing is left
    function fillUp() { while (box.scrollHeight <= box.clientHeight && loadOlder()); }

    if (state.messages.length) {
        document.getElementById('smSuggest')?.remove();
        loadOlder();
        reset.hidden = false;
    }
    box.addEventListener('scroll', () => { if (box.scrollTop < 60) loadOlder(); }, { passive: true });
    // reopen after navigation on desktop only: on phones the panel would cover the new page
    if (EMBED || (state.open && window.matchMedia('(min-width: 768px)').matches)) toggle(true, false);

    document.addEventListener('click', e => { if (e.target.closest('[data-sm-open]')) toggle(!isOpen()); });
    panel.querySelector('[data-sm-close]').addEventListener('click', closePanel);
    reset.addEventListener('click', () => {
        if (busy) return;
        state.messages = []; state.history = []; shown = 0; save();
        box.innerHTML = intro;
        reset.hidden = true;
        input.focus();
    });
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && isOpen()) closePanel(); });
    input.addEventListener('input', sync);
    box.addEventListener('click', e => { // delegated: the chips are re-created on "new chat"
        const chip = e.target.closest('.sm-chip');
        if (!chip || busy) return;
        input.value = chip.textContent.trim();
        form.requestSubmit();
    });

    form.addEventListener('submit', async e => {
        e.preventDefault();
        const message = input.value.trim();
        if (!message || busy) return;
        document.getElementById('smSuggest')?.remove();
        const asked = add(message, 'user').parentElement; // the user's row: the reply is read from here down
        remember(message, 'user');
        input.value = '';
        busy = true; sync();
        const pending = add('', 'bot');
        pending.classList.add('sm-typing');
        pending.setAttribute('aria-label', 'Sapa Madani sedang mengetik');
        pending.innerHTML = '<span></span><span></span><span></span>'; // static typing dots
        let reply;
        try {
            const res = await fetch(@json(route('assistant.chat')), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json', 'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '', // none in the embed page
                },
                body: JSON.stringify({ message, history: state.history.slice(-MAX_HISTORY) }),
            });
            const data = await res.json();
            if (res.ok && data.reply) state.history.push({ role: 'user', content: message }, { role: 'assistant', content: data.reply.slice(0, 1000) });
            reply = res.ok && data.reply ? data.reply : (res.status === 429 ? 'Terlalu banyak pesan, coba lagi sebentar.' : 'Maaf, terjadi kesalahan. Silakan coba lagi.');
        } catch (err) {
            reply = 'Koneksi gagal. Silakan coba lagi.';
        }
        pending.classList.remove('sm-typing');
        pending.removeAttribute('aria-label');
        fill(pending, reply);
        remember(reply, 'bot');
        // Long replies: put the question at the top so the answer is read from its start, not its end
        // (short ones simply end at the bottom: scrollTop is capped).
        box.scrollTop += asked.getBoundingClientRect().top - box.getBoundingClientRect().top - 8;
        busy = false; sync();
        input.focus();
    });
})();
</script>
