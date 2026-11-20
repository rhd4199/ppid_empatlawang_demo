{{-- BMKG weather: alert banner (every page), footer chip, and the home card (#bmkgCard) when present. Filled by JS from /cuaca. --}}
<div id="bmkgAlert" class="bmkg-alert" role="alert" hidden>
    <div class="container d-flex align-items-start gap-2 py-2">
        <i class="fas fa-triangle-exclamation mt-1" aria-hidden="true"></i>
        <div class="flex-grow-1"><strong data-title></strong> <span data-areas></span>
            <a href="#" data-link target="_blank" rel="noopener" class="text-white fw-semibold ms-1">Detail BMKG</a></div>
        <button type="button" class="btn-close btn-close-white" aria-label="Tutup" data-close></button>
    </div>
</div>

<style>
    .bmkg-alert { background: #b91c1c; color: #fff; font-size: .9rem; }
    .bmkg-chip { display: inline-flex; align-items: center; gap: 6px; color: rgba(255,255,255,.85); font-size: .85rem; }
    .bmkg-chip[hidden] { display: none; }
    .bmkg-card { background: linear-gradient(135deg, #0284c7, #0369a1); color: #fff; border-radius: 20px; padding: 24px; }
    .bmkg-now { background: rgba(255,255,255,.14); border-radius: 16px; padding: 18px; }
    .bmkg-now .bmkg-temp { font-size: 3.25rem; font-weight: 800; line-height: 1; }
    .bmkg-stats { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
    .bmkg-stat { background: rgba(255,255,255,.12); border-radius: 12px; padding: 8px 10px; font-size: .95rem; font-weight: 600; }
    .bmkg-stat small { display: block; font-size: .72rem; font-weight: 400; opacity: .8; }
    /* Equal columns that fill the width; below ~78px each (phones) the row scrolls sideways instead of squeezing. */
    .bmkg-slots { display: grid; grid-auto-flow: column; grid-auto-columns: minmax(78px, 1fr); gap: 8px; overflow-x: auto; padding-bottom: 4px; scroll-snap-type: x proximity; }
    .bmkg-slot { text-align: center; background: rgba(255,255,255,.12); border-radius: 14px; padding: 8px 4px; font-size: .85rem; scroll-snap-align: start; }
    .bmkg-slot .bmkg-day { font-size: .7rem; opacity: .8; min-height: 1.1em; }
    .bmkg-slot .bmkg-fa { font-size: 1.75rem; margin: 6px 0; display: block; }
    .bmkg-slot .bmkg-t { font-size: 1.05rem; font-weight: 700; }
    .bmkg-fa { width: 1.25em; text-align: center; }
    .bmkg-chip .bmkg-fa { font-size: 1rem; }
    .bmkg-now .bmkg-fa { font-size: 4rem; }
    .bmkg-fa.fa-sun { color: #fde047; }
    .bmkg-fa.fa-cloud-sun, .bmkg-fa.fa-moon, .bmkg-fa.fa-cloud-moon { color: #fef3c7; }
    .bmkg-fa.fa-cloud-bolt { color: #fcd34d; }
    .bmkg-card select { max-width: 220px; }
    .bmkg-ig { --ig: #b91c1c; --ig-soft: #fef2f2; background: #fff; color: #1e293b; border-radius: 16px; overflow: hidden; font-size: .88rem; box-shadow: 0 6px 18px rgba(15,23,42,.18); }
    .bmkg-ig-amber { --ig: #b45309; --ig-soft: #fffbeb; }
    .bmkg-ig-head { display: flex; align-items: center; gap: 12px; padding: 12px 14px; background: var(--ig); color: #fff; }
    .bmkg-ig-icon { width: 52px; height: 52px; flex: none; border-radius: 14px; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,.18); font-size: 1.7rem; color: #fde047; }
    .bmkg-ig-badge { display: inline-block; font-size: .68rem; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; opacity: .9; }
    .bmkg-ig-title { font-weight: 800; font-size: 1rem; line-height: 1.25; }
    .bmkg-ig-status { flex: none; padding: 4px 10px; border-radius: 999px; font-size: .72rem; font-weight: 700; background: #fff; color: var(--ig); }
    .bmkg-ig-status.is-live::before { content: ""; display: inline-block; width: 7px; height: 7px; margin-right: 5px; border-radius: 50%; background: currentColor; animation: bmkg-blink 1.2s infinite; }
    .bmkg-ig-status.is-past { background: rgba(255,255,255,.25); color: #fff; }
    @keyframes bmkg-blink { 50% { opacity: .25; } }
    .bmkg-ig-time { display: flex; align-items: center; gap: 12px; padding: 10px 14px; background: var(--ig-soft); }
    .bmkg-ig-time small { display: block; font-size: .68rem; color: #64748b; }
    .bmkg-ig-time strong { font-size: 1rem; color: var(--ig); white-space: nowrap; }
    .bmkg-ig-bar { flex: 1; height: 8px; border-radius: 999px; background: #e2e8f0; overflow: hidden; }
    .bmkg-ig-bar span { display: block; height: 100%; background: var(--ig); }
    .bmkg-ig-sec { padding: 10px 14px 0; display: flex; flex-wrap: wrap; gap: 6px; }
    .bmkg-ig-label { width: 100%; font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; color: #64748b; }
    .bmkg-ig-area, .bmkg-ig-chip { display: inline-flex; align-items: center; gap: 6px; padding: 5px 10px; border-radius: 999px; font-weight: 600; }
    .bmkg-ig-area { background: var(--ig); color: #fff; }
    .bmkg-ig-chip { background: var(--ig-soft); color: var(--ig); border: 1px solid currentColor; }
    .bmkg-ig-tips { width: 100%; display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 6px; }
    .bmkg-ig-tip { display: flex; align-items: center; gap: 8px; padding: 6px 8px; border-radius: 10px; background: #f1f5f9; font-size: .8rem; line-height: 1.25; }
    .bmkg-ig-tip i { width: 26px; height: 26px; flex: none; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: #fff; color: var(--ig); font-size: .8rem; }
    .bmkg-ig-more { margin: 10px 14px 12px; padding-top: 8px; border-top: 1px dashed #cbd5e1; }
    .bmkg-ig-more summary { cursor: pointer; font-weight: 700; color: var(--ig); }
    .bmkg-ig-more a { color: var(--ig); }
    @media (max-width: 575.98px) { .bmkg-ig-head { flex-wrap: wrap; } .bmkg-ig-icon { width: 44px; height: 44px; font-size: 1.4rem; } }
    @media (prefers-reduced-motion: reduce) { .bmkg-ig-status.is-live::before { animation: none; } }
</style>

<script>
(function () {
    const url = @json(route('weather.show', [], false)); // relative: works behind any host/proxy/scheme
    // Looked up on use: this partial sits above the page content, so #bmkgCard does not exist yet when the script runs.
    const getCard = () => document.getElementById('bmkgCard');
    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[c]));
    const hour = t => t.slice(11, 16);
    const night = t => { const h = +t.slice(11, 13); return h >= 18 || h < 6; };
    const fa = (desc, isNight) => /petir/i.test(desc) ? 'fa-cloud-bolt' : /hujan|gerimis/i.test(desc) ? 'fa-cloud-rain'
        : /kabut|kabur|asap/i.test(desc) ? 'fa-smog' : /cerah berawan/i.test(desc) ? (isNight ? 'fa-cloud-moon' : 'fa-cloud-sun')
        : /cerah/i.test(desc) ? (isNight ? 'fa-moon' : 'fa-sun') : 'fa-cloud';
    // One icon set for every source (BMKG's own SVGs mix styles, e.g. "Udara Kabur"; Open-Meteo has none).
    const icon = s => `<i class="fas ${fa(s.desc, night(s.time))} bmkg-fa" title="${esc(s.desc)}" aria-hidden="true"></i>`;
    const today = () => new Date().toLocaleDateString('sv-SE', {timeZone: 'Asia/Jakarta'}); // YYYY-MM-DD in WIB
    const dayDiff = t => Math.round((Date.parse(t.slice(0, 10)) - Date.parse(today())) / 864e5);
    const dayLabel = t => ({0: 'Hari ini', 1: 'Besok', 2: 'Lusa'})[dayDiff(t)]
        ?? new Date(t.slice(0, 10) + 'T00:00').toLocaleDateString('id-ID', {weekday: 'short'});
    const longDate = ymd => new Date(ymd + 'T00:00').toLocaleDateString('id-ID', {weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'});
    const windDir = w => ({N: 'Utara', NE: 'Timur Laut', E: 'Timur', SE: 'Tenggara', S: 'Selatan', SW: 'Barat Daya', W: 'Barat', NW: 'Barat Laut'})[w] ?? w;
    // Storage can throw (in-app browsers, private mode); never let it break the widget.
    const store = {
        get: k => { try { return sessionStorage.getItem(k); } catch (e) { return null; } },
        set: (k, v) => { try { sessionStorage.setItem(k, v); } catch (e) {} },
    };
    let bannerDone = false;

    function banner(alerts) {
        const el = document.getElementById('bmkgAlert');
        if (bannerDone || !el || !alerts.length || store.get('bmkgAlertClosed') === alerts[0].title) return;
        bannerDone = true;
        el.querySelector('[data-title]').textContent = alerts[0].title + '.';
        el.querySelector('[data-areas]').textContent = 'Wilayah: ' + alerts[0].areas.join(', ') + '.';
        el.querySelector('[data-link]').href = alerts[0].link || 'https://www.bmkg.go.id/peringatan-dini/nowcast';
        el.querySelector('[data-close]').onclick = () => { el.hidden = true; store.set('bmkgAlertClosed', alerts[0].title); };
        el.hidden = false;
    }

    function chip(d) {
        const now = d.slots[0];
        document.querySelectorAll('[data-bmkg-chip]').forEach(el => {
            if (!now) return;
            el.innerHTML = `${icon(now)}<span>${esc(d.name)} ${esc(now.temp)}°C · ${esc(now.desc)}</span><small class="opacity-75">(${esc(now.source || 'BMKG')})</small>`;
            el.hidden = false;
        });
    }

    const title = s => s.toLowerCase().replace(/\b\w+/g, w => /^[ivx]+$/.test(w) ? w.toUpperCase() : w[0].toUpperCase() + w.slice(1)); // BANYUASIN III → Banyuasin III
    // Infographic per alert: hazard icon + status, valid-time bar, Empat Lawang districts, impact chips, advice.
    // Full BMKG text + every Sumsel district stay folded in "Selengkapnya".
    const wibMin = s => { const m = /(\d{1,2})[.:](\d{2})/.exec(s || ''); return m ? +m[1] * 60 + +m[2] : null; };
    const nowMin = () => wibMin(new Date().toLocaleTimeString('en-GB', {timeZone: 'Asia/Jakarta', hour12: false}));
    const IMPACT = [[/jarak pandang/i, 'fa-eye-slash'], [/angin/i, 'fa-wind'], [/banjir|genangan/i, 'fa-house-flood-water'],
        [/longsor/i, 'fa-hill-rockslide'], [/pohon/i, 'fa-tree'], [/petir|kilat/i, 'fa-bolt'], [/licin/i, 'fa-road']];
    const cap = s => s.charAt(0).toUpperCase() + s.slice(1);
    function advice(t) {
        const tips = [['fa-house', 'Kurangi aktivitas di luar ruangan']];
        if (/petir|angin/i.test(t)) tips.push(['fa-tree', 'Jauhi pohon besar & papan reklame']);
        if (/hujan/i.test(t)) tips.push(['fa-water', 'Waspada genangan, banjir & longsor']);
        tips.push(['fa-tower-broadcast', 'Pantau info terbaru BMKG']);
        return tips;
    }
    function warn(a) {
        const all = a.all_areas || [];
        const link = /^https:\/\//.test(a.link || '') ? a.link : 'https://www.bmkg.go.id/peringatan-dini/nowcast';
        const severe = /lebat|ekstrem|kencang|es\b/i.test(a.title);
        const [s, e, n] = [wibMin(a.start), wibMin(a.end), nowMin()];
        const status = s === null || e === null || e <= s ? null
            : n < s ? ['soon', 'Akan terjadi'] : n <= e ? ['live', 'Sedang berlangsung'] : ['past', 'Telah berakhir'];
        const pct = status ? Math.min(100, Math.max(0, Math.round((n - s) / (e - s) * 100))) : 0;
        const impacts = (a.impact || '').split(/,\s*(?:dan\s+)?|\s+dan\s+/).map(x => x.trim()).filter(Boolean);
        const chips = (list, cls) => list.map(([ic, txt]) => `<span class="${cls}"><i class="fas ${ic}" aria-hidden="true"></i>${esc(txt)}</span>`).join('');
        return `<article class="bmkg-ig ${severe ? 'bmkg-ig-red' : 'bmkg-ig-amber'} mb-2">
            <header class="bmkg-ig-head">
                <span class="bmkg-ig-icon"><i class="fas ${fa(a.title, false)}" aria-hidden="true"></i></span>
                <div class="flex-grow-1" style="min-width: 0">
                    <span class="bmkg-ig-badge"><i class="fas fa-triangle-exclamation me-1" aria-hidden="true"></i>Peringatan Dini BMKG</span>
                    <div class="bmkg-ig-title">${esc(a.title)}</div>
                </div>
                ${status ? `<span class="bmkg-ig-status is-${status[0]}">${status[1]}</span>` : ''}
            </header>
            ${a.start || a.end ? `<div class="bmkg-ig-time">
                <div><small>Mulai</small><strong>${esc(a.start || '–')}</strong></div>
                <div class="bmkg-ig-bar" aria-hidden="true"><span style="width:${pct}%"></span></div>
                <div class="text-end"><small>Selesai</small><strong>${esc(a.end || '–')}</strong></div></div>` : ''}
            <div class="bmkg-ig-sec"><div class="bmkg-ig-label"><i class="fas fa-location-dot me-1" aria-hidden="true"></i>Kecamatan terdampak di Empat Lawang</div>
                ${chips(a.areas.map(x => ['fa-map-pin', 'Kec. ' + title(x)]), 'bmkg-ig-area')}</div>
            ${impacts.length ? `<div class="bmkg-ig-sec"><div class="bmkg-ig-label">Potensi dampak</div>
                ${chips(impacts.map(x => [(IMPACT.find(([re]) => re.test(x)) || [, 'fa-circle-exclamation'])[1], cap(x)]), 'bmkg-ig-chip')}</div>` : ''}
            <div class="bmkg-ig-sec"><div class="bmkg-ig-label">Imbauan</div>
                <div class="bmkg-ig-tips">${chips(advice(a.title + ' ' + (a.impact || '')), 'bmkg-ig-tip')}</div></div>
            <details class="bmkg-ig-more"><summary>Selengkapnya${all.length ? ` (${all.length} kecamatan di Sumsel)` : ''}</summary>
                ${all.length ? `<div class="mt-1">${all.map(x => a.areas.includes(x) ? `<strong>${esc(title(x))}</strong>` : esc(title(x))).join(', ')}</div>` : ''}
                <p class="mt-1 mb-1" style="white-space: pre-line">${esc(a.description)}</p>
                <a href="${esc(link)}" target="_blank" rel="noopener" class="fw-semibold">Lihat di BMKG</a>
            </details></article>`;
    }

    function render(d) {
        const card = getCard();
        if (!card) return;
        const now = d.slots[0];
        const select = card.querySelector('select');
        if (!select.options.length) {
            Object.entries(d.districts).forEach(([code, name]) => select.add(new Option(name, code)));
            select.onchange = () => load(select.value);
        }
        select.value = d.district;
        const date = card.querySelector('[data-date]');
        if (date) date.textContent = `Kec. ${d.name} · ${longDate(today())}`;
        card.querySelector('[data-now]').innerHTML = now
            ? `<div class="small opacity-75 mb-2">Sekarang (prakiraan ${esc(hour(now.time))} WIB)</div>
               <div class="d-flex align-items-center gap-3 mb-3">${icon(now)}<div><div class="bmkg-temp">${esc(now.temp)}°C</div>
               <div class="fw-semibold fs-5">${esc(now.desc)}</div></div></div>
               <div class="bmkg-stats"><div class="bmkg-stat"><small>Kelembapan</small><i class="fas fa-droplet me-1"></i>${esc(now.humidity)}%</div>
               <div class="bmkg-stat"><small>Angin dari ${esc(windDir(now.wind_dir))}</small><i class="fas fa-wind me-1"></i>${esc(now.wind)} km/j</div></div>
               ${d.updated ? `<div class="small opacity-75 mt-2"><i class="fas fa-rotate me-1"></i>Diperbarui ${esc(hour(d.updated))} WIB</div>` : ''}`
            : '<p class="mb-0 opacity-75">Data cuaca belum tersedia. Coba lagi nanti.</p>';
        const src = card.querySelector('[data-source]');
        if (src) src.textContent = d.source;
        // Day label only where the day changes (first slot, then e.g. "Besok" after midnight).
        card.querySelector('[data-slots]').innerHTML = d.slots.slice(1).map((s, i, a) =>
            `<div class="bmkg-slot"><div class="bmkg-day">${i === 0 || a[i - 1].time.slice(0, 10) !== s.time.slice(0, 10) ? esc(dayLabel(s.time)) : ''}</div>
             <div class="fw-semibold">${esc(hour(s.time))}</div>${icon(s)}<div class="bmkg-t">${esc(s.temp)}°</div>
             <div class="small opacity-75 text-truncate" title="${esc(s.desc)}">${esc(s.desc)}</div></div>`).join('');
        card.querySelector('[data-warn]').innerHTML = d.alerts.length
            ? d.alerts.map(warn).join('')
            : '<div class="small opacity-75"><i class="fas fa-circle-check me-1"></i>Tidak ada peringatan dini cuaca BMKG untuk Empat Lawang saat ini.</div>';
    }

    function failed(kec, retried) {
        if (!retried) return setTimeout(() => load(kec, true), 4000); // one automatic retry
        const card = getCard();
        const box = card && card.querySelector('[data-now]');
        if (!box) return;
        box.innerHTML = '<p class="mb-2 opacity-75">Data cuaca gagal dimuat.</p><button type="button" class="btn btn-light btn-sm rounded-pill px-3">Coba lagi</button>';
        box.querySelector('button').onclick = () => { box.innerHTML = '<p class="mb-0 opacity-75">Memuat data cuaca…</p>'; load(kec, true); };
    }

    function load(kec, retried) {
        fetch(url + (kec ? '?kec=' + encodeURIComponent(kec) : ''), {headers: {'Accept': 'application/json'}})
            .then(r => r.ok ? r.json() : Promise.reject(r.status))
            .then(d => {
                render(d); // card first: a banner/chip problem must not leave it on "Memuat…"
                if (!kec) chip(d);
                banner(d.alerts);
            })
            .catch(e => { console.warn('Cuaca gagal dimuat', e); failed(kec, retried); });
    }
    document.addEventListener('DOMContentLoaded', () => load());
})();
</script>
