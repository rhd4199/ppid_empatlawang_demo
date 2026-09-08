{{-- Aksesibilitas: panel bantuan visual + pembaca teks (Web Speech API bawaan browser) --}}
<svg aria-hidden="true" focusable="false" style="position:absolute;width:0;height:0;overflow:hidden">
    <defs>
        <filter id="a11y-protanopia">
            <feColorMatrix type="matrix" values="0.567 0.433 0 0 0  0.558 0.442 0 0 0  0 0.242 0.758 0 0  0 0 0 1 0"/>
        </filter>
        <filter id="a11y-deuteranopia">
            <feColorMatrix type="matrix" values="0.625 0.375 0 0 0  0.7 0.3 0 0 0  0 0.3 0.7 0 0  0 0 0 1 0"/>
        </filter>
        <filter id="a11y-tritanopia">
            <feColorMatrix type="matrix" values="0.95 0.05 0 0 0  0 0.433 0.567 0 0  0 0.475 0.525 0 0  0 0 0 1 0"/>
        </filter>
    </defs>
</svg>

<div class="a11y-widget">
    <button type="button" class="a11y-toggle" id="a11yToggle" aria-expanded="false" aria-controls="a11yPanel"
            title="Aksesibilitas Website" aria-label="Buka panel aksesibilitas website">
        <i class="fas fa-universal-access" aria-hidden="true"></i>
    </button>

    <div class="a11y-panel" id="a11yPanel" role="dialog" aria-label="Panel Aksesibilitas" hidden>
        <div class="a11y-panel-head">
            <span><i class="fas fa-universal-access me-2" aria-hidden="true"></i>Aksesibilitas</span>
            <button type="button" class="a11y-close" id="a11yClose" aria-label="Tutup panel aksesibilitas">&times;</button>
        </div>

        <div class="a11y-panel-body">
            <p class="a11y-group-title">Ukuran Teks</p>
            <div class="a11y-row">
                <button type="button" class="a11y-btn" data-a11y-font="-1" aria-label="Perkecil ukuran teks">A&minus;</button>
                <button type="button" class="a11y-btn" data-a11y-font="0" aria-label="Ukuran teks normal">A</button>
                <button type="button" class="a11y-btn" data-a11y-font="1" aria-label="Perbesar ukuran teks">A+</button>
            </div>

            <p class="a11y-group-title">Bantuan Penglihatan</p>
            <div class="a11y-grid">
                <button type="button" class="a11y-btn" data-a11y-toggle="contrast"><i class="fas fa-circle-half-stroke" aria-hidden="true"></i> Kontras Tinggi</button>
                <button type="button" class="a11y-btn" data-a11y-toggle="links"><i class="fas fa-link" aria-hidden="true"></i> Tandai Tautan</button>
                <button type="button" class="a11y-btn" data-a11y-toggle="readable"><i class="fas fa-font" aria-hidden="true"></i> Font Terbaca</button>
                <button type="button" class="a11y-btn" data-a11y-toggle="motion"><i class="fas fa-pause" aria-hidden="true"></i> Hentikan Animasi</button>
            </div>

            <p class="a11y-group-title">Filter Warna (Buta Warna)</p>
            <div class="a11y-grid">
                <button type="button" class="a11y-btn" data-a11y-filter="protanopia">Protanopia</button>
                <button type="button" class="a11y-btn" data-a11y-filter="deuteranopia">Deuteranopia</button>
                <button type="button" class="a11y-btn" data-a11y-filter="tritanopia">Tritanopia</button>
                <button type="button" class="a11y-btn" data-a11y-filter="grayscale">Skala Abu-abu</button>
                <button type="button" class="a11y-btn" data-a11y-filter="saturate">Saturasi Tinggi</button>
                <button type="button" class="a11y-btn" data-a11y-filter="invert">Warna Terbalik</button>
            </div>

            <p class="a11y-group-title">Pembaca Teks (Suara)</p>
            <div class="a11y-row">
                <button type="button" class="a11y-btn flex-grow-1" id="a11ySpeak"><i class="fas fa-volume-high" aria-hidden="true"></i> Bacakan Halaman</button>
                <button type="button" class="a11y-btn" id="a11yStop" aria-label="Hentikan pembacaan"><i class="fas fa-stop" aria-hidden="true"></i></button>
            </div>
            <p class="a11y-hint">Blok/sorot sebagian teks lalu tekan <em>Bacakan</em> untuk membaca bagian itu saja.</p>

            <button type="button" class="a11y-btn a11y-reset w-100" id="a11yReset"><i class="fas fa-rotate-left" aria-hidden="true"></i> Reset Pengaturan</button>
        </div>
    </div>
</div>

<style>
    .a11y-widget { position: fixed; right: 18px; bottom: 18px; z-index: 1080; }
    .a11y-toggle {
        width: 52px; height: 52px; border-radius: 50%; border: 0;
        background: var(--primary-color, #0284c7); color: #fff; font-size: 1.5rem;
        box-shadow: 0 6px 18px rgba(0,0,0,.25); cursor: pointer;
    }
    .a11y-toggle:hover, .a11y-toggle:focus-visible { background: var(--accent-color, #0369a1); }
    .a11y-panel {
        position: absolute; right: 0; bottom: 64px; width: 320px; max-width: calc(100vw - 36px);
        max-height: 78vh; overflow-y: auto; background: #fff; color: #212529;
        border-radius: 14px; box-shadow: 0 10px 35px rgba(0,0,0,.25);
    }
    .a11y-panel-head {
        display: flex; align-items: center; justify-content: space-between;
        background: var(--primary-color, #0284c7); color: #fff;
        padding: 12px 16px; font-weight: 600; border-radius: 14px 14px 0 0;
    }
    .a11y-close { background: transparent; border: 0; color: #fff; font-size: 1.4rem; line-height: 1; cursor: pointer; }
    .a11y-panel-body { padding: 14px 16px 18px; }
    .a11y-group-title { font-size: .78rem; text-transform: uppercase; letter-spacing: .04em; color: #6c757d; margin: 12px 0 6px; font-weight: 600; }
    .a11y-panel-body > .a11y-group-title:first-child { margin-top: 0; }
    .a11y-row { display: flex; gap: 8px; }
    .a11y-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
    .a11y-btn {
        background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 10px;
        padding: 9px 10px; font-size: .85rem; color: #1f2937; cursor: pointer; text-align: center;
        flex: 1; transition: .2s;
    }
    .a11y-btn:hover { background: #e2e8f0; }
    .a11y-btn.is-active { background: var(--primary-color, #0284c7); border-color: var(--primary-color, #0284c7); color: #fff; }
    .a11y-reset { margin-top: 14px; background: #fee2e2; border-color: #fecaca; color: #991b1b; }
    .a11y-hint { font-size: .75rem; color: #6c757d; margin: 6px 0 0; }

    /* Skip link */
    .a11y-skip {
        position: absolute; left: 8px; top: -60px; z-index: 1090;
        background: var(--primary-color, #0284c7); color: #fff; padding: 10px 16px; border-radius: 0 0 8px 8px;
        transition: top .2s;
    }
    .a11y-skip:focus { top: 0; color: #fff; }

    /* Mode aksesibilitas */
    html.a11y-contrast body { background: #000 !important; color: #fff !important; }
    html.a11y-contrast :is(p, span, li, td, th, small, label, div, h1, h2, h3, h4, h5, h6) { color: #fff !important; }
    html.a11y-contrast :is(.card, .navbar, footer, .modal-content, .list-group-item, .page-header, main, section) { background: #000 !important; border-color: #ffd400 !important; }
    html.a11y-contrast a, html.a11y-contrast .nav-link { color: #ffd400 !important; }
    html.a11y-contrast :is(.btn, .badge) { background: #ffd400 !important; color: #000 !important; border-color: #ffd400 !important; }
    html.a11y-contrast img { filter: grayscale(20%) contrast(120%); }

    html.a11y-links a { text-decoration: underline !important; text-underline-offset: 3px; font-weight: 600; }
    html.a11y-readable body, html.a11y-readable :is(h1,h2,h3,h4,h5,h6,p,li,a,span,td,th,label,button,input,textarea) {
        font-family: Verdana, "Segoe UI", Tahoma, sans-serif !important;
        letter-spacing: .02em; line-height: 1.75;
    }
    html.a11y-motion *, html.a11y-motion *::before, html.a11y-motion *::after {
        animation: none !important; transition: none !important; scroll-behavior: auto !important;
    }

    html.a11y-filter-protanopia body { filter: url(#a11y-protanopia); }
    html.a11y-filter-deuteranopia body { filter: url(#a11y-deuteranopia); }
    html.a11y-filter-tritanopia body { filter: url(#a11y-tritanopia); }
    html.a11y-filter-grayscale body { filter: grayscale(100%); }
    html.a11y-filter-saturate body { filter: saturate(210%) contrast(105%); }
    html.a11y-filter-invert body { filter: invert(100%) hue-rotate(180deg); }

    /* Fokus keyboard selalu terlihat */
    a:focus-visible, button:focus-visible, input:focus-visible,
    select:focus-visible, textarea:focus-visible, [tabindex]:focus-visible {
        outline: 3px solid #ffd400; outline-offset: 2px;
    }

    @media print { .a11y-widget { display: none; } }
</style>

<script>
(function () {
    var KEY = 'ppid-a11y';
    var root = document.documentElement;
    var panel = document.getElementById('a11yPanel');
    var toggle = document.getElementById('a11yToggle');
    var state = { font: 0, contrast: false, links: false, readable: false, motion: false, filter: null };

    try { Object.assign(state, JSON.parse(localStorage.getItem(KEY) || '{}')); } catch (e) {}

    function save() { try { localStorage.setItem(KEY, JSON.stringify(state)); } catch (e) {} }

    function apply() {
        root.style.fontSize = state.font ? (100 + state.font * 12.5) + '%' : '';
        ['contrast', 'links', 'readable', 'motion'].forEach(function (k) {
            root.classList.toggle('a11y-' + k, !!state[k]);
        });
        root.className = root.className.replace(/\ba11y-filter-\S+/g, '').trim();
        if (state.filter) root.classList.add('a11y-filter-' + state.filter);

        document.querySelectorAll('[data-a11y-toggle]').forEach(function (b) {
            b.classList.toggle('is-active', !!state[b.dataset.a11yToggle]);
        });
        document.querySelectorAll('[data-a11y-filter]').forEach(function (b) {
            b.classList.toggle('is-active', state.filter === b.dataset.a11yFilter);
        });
        save();
    }

    toggle.addEventListener('click', function () {
        var open = panel.hasAttribute('hidden');
        if (open) { panel.removeAttribute('hidden'); } else { panel.setAttribute('hidden', ''); }
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
    document.getElementById('a11yClose').addEventListener('click', function () {
        panel.setAttribute('hidden', '');
        toggle.setAttribute('aria-expanded', 'false');
        toggle.focus();
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !panel.hasAttribute('hidden')) {
            panel.setAttribute('hidden', '');
            toggle.setAttribute('aria-expanded', 'false');
        }
    });

    document.querySelectorAll('[data-a11y-font]').forEach(function (b) {
        b.addEventListener('click', function () {
            var step = parseInt(b.dataset.a11yFont, 10);
            state.font = step === 0 ? 0 : Math.max(-1, Math.min(3, state.font + step));
            apply();
        });
    });
    document.querySelectorAll('[data-a11y-toggle]').forEach(function (b) {
        b.addEventListener('click', function () { state[b.dataset.a11yToggle] = !state[b.dataset.a11yToggle]; apply(); });
    });
    document.querySelectorAll('[data-a11y-filter]').forEach(function (b) {
        b.addEventListener('click', function () {
            state.filter = state.filter === b.dataset.a11yFilter ? null : b.dataset.a11yFilter;
            apply();
        });
    });
    document.getElementById('a11yReset').addEventListener('click', function () {
        state = { font: 0, contrast: false, links: false, readable: false, motion: false, filter: null };
        stopSpeech();
        apply();
    });

    // Pembaca teks: Web Speech API bawaan browser (tanpa library tambahan)
    var speakBtn = document.getElementById('a11ySpeak');
    var synth = window.speechSynthesis;

    function stopSpeech() {
        if (synth) synth.cancel();
        speakBtn.innerHTML = '<i class="fas fa-volume-high" aria-hidden="true"></i> Bacakan Halaman';
    }

    function pageText() {
        var selected = String(window.getSelection() || '').trim();
        if (selected) return selected;
        var main = document.getElementById('konten-utama') || document.body;
        return (main.innerText || '').replace(/\s+/g, ' ').trim();
    }

    speakBtn.addEventListener('click', function () {
        if (!synth) { alert('Peramban Anda belum mendukung pembaca teks.'); return; }
        if (synth.speaking) { stopSpeech(); return; }

        var text = pageText();
        if (!text) return;
        // ponytail: pecah per ~200 karakter, sebagian browser memotong utterance panjang
        var chunks = (text.match(/[^.!?]+[.!?]*\s*/g) || [text]).reduce(function (acc, s) {
            var last = acc[acc.length - 1];
            if (last && (last + s).length < 200) acc[acc.length - 1] = last + s;
            else acc.push(s);
            return acc;
        }, []);
        chunks.forEach(function (chunk, i) {
            var u = new SpeechSynthesisUtterance(chunk);
            u.lang = 'id-ID';
            u.rate = 0.95;
            if (i === chunks.length - 1) u.onend = stopSpeech;
            synth.speak(u);
        });
        speakBtn.innerHTML = '<i class="fas fa-volume-xmark" aria-hidden="true"></i> Hentikan Pembacaan';
    });
    document.getElementById('a11yStop').addEventListener('click', stopSpeech);
    window.addEventListener('beforeunload', stopSpeech);

    apply();
})();
</script>
