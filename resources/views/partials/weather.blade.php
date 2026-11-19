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
    .bmkg-chip img { width: 22px; height: 22px; }
    .bmkg-card { background: linear-gradient(135deg, #0284c7, #0369a1); color: #fff; border-radius: 20px; padding: 24px; }
    .bmkg-now img { width: 72px; height: 72px; }
    .bmkg-now .bmkg-temp { font-size: 3rem; font-weight: 800; line-height: 1; }
    .bmkg-slots { display: flex; gap: 10px; overflow-x: auto; padding-bottom: 4px; }
    .bmkg-slot { flex: 0 0 auto; min-width: 78px; text-align: center; background: rgba(255,255,255,.12); border-radius: 14px; padding: 8px 6px; font-size: .8rem; }
    .bmkg-slot img { width: 34px; height: 34px; }
    .bmkg-card select { max-width: 220px; }
    .bmkg-warn { background: rgba(255,255,255,.95); color: #991b1b; border-radius: 14px; padding: 10px 14px; font-size: .88rem; }
</style>

<script>
(function () {
    const url = @json(route('weather.show'));
    const card = document.getElementById('bmkgCard');
    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[c]));
    const hour = t => t.slice(11, 16);
    const icon = (src, alt) => src && /^https:\/\//.test(src) ? `<img src="${esc(src)}" alt="${esc(alt)}" loading="lazy">` : '';
    let bannerDone = false;

    function banner(alerts) {
        const el = document.getElementById('bmkgAlert');
        if (bannerDone || !el || !alerts.length || sessionStorage.getItem('bmkgAlertClosed') === alerts[0].title) return;
        bannerDone = true;
        el.querySelector('[data-title]').textContent = alerts[0].title + '.';
        el.querySelector('[data-areas]').textContent = 'Wilayah: ' + alerts[0].areas.join(', ') + '.';
        el.querySelector('[data-link]').href = alerts[0].link || 'https://www.bmkg.go.id/peringatan-dini/nowcast';
        el.querySelector('[data-close]').onclick = () => { el.hidden = true; sessionStorage.setItem('bmkgAlertClosed', alerts[0].title); };
        el.hidden = false;
    }

    function chip(d) {
        const now = d.slots[0];
        document.querySelectorAll('[data-bmkg-chip]').forEach(el => {
            if (!now) return;
            el.innerHTML = `${icon(now.icon, now.desc)}<span>${esc(d.name)} ${esc(now.temp)}°C · ${esc(now.desc)}</span><small class="opacity-75">(BMKG)</small>`;
            el.hidden = false;
        });
    }

    function render(d) {
        if (!card) return;
        const now = d.slots[0];
        const select = card.querySelector('select');
        if (!select.options.length) {
            Object.entries(d.districts).forEach(([code, name]) => select.add(new Option(name, code)));
            select.onchange = () => load(select.value);
        }
        select.value = d.district;
        card.querySelector('[data-now]').innerHTML = now
            ? `<div class="d-flex align-items-center gap-3">${icon(now.icon, now.desc)}<div><div class="bmkg-temp">${esc(now.temp)}°C</div>
               <div class="fw-semibold">${esc(now.desc)}</div></div></div>
               <div class="small mt-2 opacity-75"><i class="fas fa-droplet me-1"></i>${esc(now.humidity)}% &nbsp; <i class="fas fa-wind me-1"></i>${esc(now.wind)} km/j ${esc(now.wind_dir)}</div>`
            : '<p class="mb-0 opacity-75">Data cuaca BMKG belum tersedia. Coba lagi nanti.</p>';
        card.querySelector('[data-slots]').innerHTML = d.slots.slice(1).map(s =>
            `<div class="bmkg-slot"><div class="fw-semibold">${esc(hour(s.time))}</div>${icon(s.icon, s.desc)}<div>${esc(s.temp)}°</div></div>`).join('');
        card.querySelector('[data-warn]').innerHTML = d.alerts.length
            ? d.alerts.map(a => `<div class="bmkg-warn mb-2"><i class="fas fa-triangle-exclamation me-1"></i><strong>${esc(a.title)}</strong><br>${esc(a.description)}</div>`).join('')
            : '<div class="small opacity-75"><i class="fas fa-circle-check me-1"></i>Tidak ada peringatan dini cuaca BMKG untuk Empat Lawang saat ini.</div>';
    }

    function load(kec) {
        fetch(url + (kec ? '?kec=' + encodeURIComponent(kec) : ''), {headers: {'Accept': 'application/json'}})
            .then(r => r.ok ? r.json() : Promise.reject(r.status))
            .then(d => { banner(d.alerts); if (!kec) chip(d); render(d); })
            .catch(() => {});
    }
    document.addEventListener('DOMContentLoaded', () => load());
})();
</script>
