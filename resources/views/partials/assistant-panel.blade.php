{{-- Sapa Madani chat panel (used when no WhatsApp number is configured). Opened by any [data-sm-open] button. --}}
<div class="sm-panel" id="smPanel" role="dialog" aria-labelledby="smTitle" hidden>
    <div class="sm-head">
        <span class="sm-head-avatar" aria-hidden="true"><i class="fas fa-robot"></i><span class="sm-online-dot"></span></span>
        <div class="sm-head-text">
            <strong id="smTitle">Sapa Madani</strong>
            <small><span class="sm-online">Online</span> · Asisten AI PPID Empat Lawang</small>
        </div>
        <button type="button" class="sm-close" data-sm-close aria-label="Tutup">&times;</button>
    </div>
    <div class="sm-body" id="smMessages" aria-live="polite">
        <div class="sm-row sm-row-bot">
            <span class="sm-mini" aria-hidden="true"><i class="fas fa-robot"></i></span>
            <div class="sm-msg sm-bot">Halo! Saya Sapa Madani. Ada yang bisa saya bantu terkait informasi publik Kabupaten Empat Lawang?</div>
        </div>
    </div>
    <form class="sm-form" id="smForm">
        <input type="text" id="smInput" maxlength="1000" placeholder="Tulis pertanyaan..." autocomplete="off" aria-label="Pertanyaan" required>
        <button type="submit" class="sm-send" aria-label="Kirim" disabled><i class="fas fa-paper-plane" aria-hidden="true"></i></button>
    </form>
    <p class="sm-note">Jawaban AI bisa keliru — untuk permohonan resmi gunakan menu <a href="{{ route('request.create') }}">Permohonan</a>.</p>
</div>

<style>
    .sm-panel {
        position: fixed; right: 18px; bottom: 150px; z-index: 1085; width: 370px; max-width: calc(100vw - 24px);
        height: 500px; max-height: calc(100vh - 170px); display: flex; flex-direction: column;
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
        background: rgba(255,255,255,.16); border: 1px solid rgba(255,255,255,.35); font-size: 1.15rem;
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
        background: linear-gradient(135deg, #0369a1, #075985); color: #fff; font-size: .7rem;
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
    .sm-send {
        width: 42px; height: 42px; flex: none; border: 0; border-radius: 50%; color: #fff;
        background: linear-gradient(135deg, #0369a1, #075985); transition: transform .15s, opacity .15s;
    }
    .sm-send:not(:disabled):hover { transform: scale(1.06); }
    .sm-send:disabled { background: #cbd5e1; color: #fff; cursor: not-allowed; }
    .sm-note { margin: 0; padding: 0 14px 10px; background: #fff; color: #64748b; font-size: .7rem; line-height: 1.35; text-align: center; }
    .sm-note a { color: #0369a1; font-weight: 600; }
    @media (max-width: 767.98px) {
        .sm-panel { left: 12px; right: 12px; width: auto; bottom: 76px; max-height: calc(100vh - 140px); max-height: calc(100dvh - 140px); transform-origin: bottom center; }
    }
    @media (prefers-reduced-motion: reduce) {
        .sm-panel, .sm-row, .sm-typing span, .sm-send, .sm-close { animation: none !important; transition: none !important; }
    }
</style>

<script>
(function () {
    const panel = document.getElementById('smPanel');
    const box = document.getElementById('smMessages');
    const form = document.getElementById('smForm');
    const input = document.getElementById('smInput');
    const send = form.querySelector('.sm-send');
    let busy = false;

    function toggle(show) {
        // queried at click time: the mobile bottom-bar opener is rendered after this script
        document.querySelectorAll('[data-sm-open]').forEach(b => b.setAttribute('aria-expanded', show));
        if (show) {
            panel.hidden = false;
            requestAnimationFrame(() => panel.classList.add('is-open'));
            input.focus();
        } else {
            panel.classList.remove('is-open');
            setTimeout(() => { if (!panel.classList.contains('is-open')) panel.hidden = true; }, 200); // after the fade-out
        }
    }
    const isOpen = () => panel.classList.contains('is-open');
    const sync = () => { send.disabled = busy || !input.value.trim(); };
    function add(text, who) {
        const row = document.createElement('div');
        row.className = 'sm-row sm-row-' + who;
        if (who === 'bot') row.innerHTML = '<span class="sm-mini" aria-hidden="true"><i class="fas fa-robot"></i></span>'; // static markup only
        const el = document.createElement('div');
        el.className = 'sm-msg sm-' + who;
        el.textContent = text; // never innerHTML: replies come from an external API
        row.appendChild(el);
        box.appendChild(row);
        box.scrollTop = box.scrollHeight;
        return el;
    }

    document.addEventListener('click', e => { if (e.target.closest('[data-sm-open]')) toggle(!isOpen()); });
    panel.querySelector('[data-sm-close]').addEventListener('click', () => toggle(false));
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && isOpen()) toggle(false); });
    input.addEventListener('input', sync);

    form.addEventListener('submit', async e => {
        e.preventDefault();
        const message = input.value.trim();
        if (!message || busy) return;
        add(message, 'user');
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
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ message }),
            });
            const data = await res.json();
            reply = res.ok && data.reply ? data.reply : (res.status === 429 ? 'Terlalu banyak pesan, coba lagi sebentar.' : 'Maaf, terjadi kesalahan. Silakan coba lagi.');
        } catch (err) {
            reply = 'Koneksi gagal. Silakan coba lagi.';
        }
        pending.classList.remove('sm-typing');
        pending.removeAttribute('aria-label');
        pending.textContent = reply;
        box.scrollTop = box.scrollHeight;
        busy = false; sync();
        input.focus();
    });
})();
</script>
