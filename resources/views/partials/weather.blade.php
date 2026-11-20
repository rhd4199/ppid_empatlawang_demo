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
    .bmkg-warn { background: rgba(255,255,255,.95); color: #991b1b; border-radius: 14px; padding: 10px 14px; font-size: .88rem; }
    .bmkg-warn summary { cursor: pointer; font-weight: 600; }
    .bmkg-warn a { color: #991b1b; }
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
    // Short box: title, valid time, impact, which Empat Lawang districts; full BMKG text + all areas folded away.
    function warn(a) {
        const all = a.all_areas || [];
        const time = a.start && a.end ? `${a.start.replace(/ WI\w+$/, '')}–${a.end}` : a.end ? `s.d. ${a.end}` : '';
        const link = /^https:\/\//.test(a.link || '') ? a.link : 'https://www.bmkg.go.id/peringatan-dini/nowcast';
        return `<div class="bmkg-warn mb-2">
            <div class="fw-bold"><i class="fas fa-triangle-exclamation me-1"></i>${esc(a.title)}</div>
            ${time ? `<div><i class="far fa-clock me-1"></i>Berlaku ${esc(time)}</div>` : ''}
            <div><i class="fas fa-location-dot me-1"></i>Termasuk Empat Lawang: <strong>Kec. ${esc(a.areas.map(title).join(', '))}</strong></div>
            ${a.impact ? `<div>Dampak: ${esc(a.impact)}</div>` : ''}
            <details class="mt-1"><summary>Selengkapnya${all.length ? ` (${all.length} kecamatan)` : ''}</summary>
                ${all.length ? `<div class="mt-1">${all.map(n => a.areas.includes(n) ? `<strong>${esc(title(n))}</strong>` : esc(title(n))).join(', ')}</div>` : ''}
                <p class="mt-1 mb-1" style="white-space: pre-line">${esc(a.description)}</p>
                <a href="${esc(link)}" target="_blank" rel="noopener" class="fw-semibold">Lihat di BMKG</a>
            </details></div>`;
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
