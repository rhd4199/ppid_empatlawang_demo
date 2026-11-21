{{-- BNPB disaster events in and around Empat Lawang (home). Filled by JS from /bencana so a slow BNPB never delays the page. --}}
<div class="bnpb-card mb-5" id="bnpbCard">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
        <div>
            <h4 class="fw-bold mb-1"><i class="fas fa-house-flood-water me-2 text-danger"></i>Informasi Bencana Sekitar Empat Lawang</h4>
            <small class="text-muted" data-scope>Kejadian bencana tercatat BNPB di Empat Lawang dan kabupaten sekitarnya</small>
        </div>
        <a href="https://inarisk.bnpb.go.id" target="_blank" rel="noopener" class="btn btn-sm btn-outline-danger rounded-pill">
            <i class="fas fa-map-location-dot me-1"></i>Cek risiko wilayah (InaRISK)
        </a>
    </div>
    <div data-list><p class="text-muted mb-0">Memuat data bencana…</p></div>
    <small class="d-block mt-3 text-muted">Sumber: BNPB (Badan Nasional Penanggulangan Bencana) &middot;
        <a href="https://gis.bnpb.go.id" target="_blank" rel="noopener">gis.bnpb.go.id</a><span data-updated></span></small>
</div>

<style>
    .bnpb-card { background: #fff; border-radius: 20px; padding: 24px; box-shadow: 0 6px 24px rgba(15,23,42,.08); border: 1px solid #fee2e2; }
    .bnpb-item { display: flex; gap: 14px; padding: 14px 0; border-top: 1px solid #f1f5f9; }
    .bnpb-item:first-child { border-top: 0; padding-top: 4px; }
    .bnpb-icon { width: 44px; height: 44px; flex: none; border-radius: 12px; display: flex; align-items: center; justify-content: center; background: #fef2f2; color: #b91c1c; font-size: 1.2rem; }
    .bnpb-item.is-local .bnpb-icon { background: #b91c1c; color: #fff; }
    .bnpb-meta { font-size: .8rem; color: #64748b; }
    .bnpb-chips { display: flex; flex-wrap: wrap; gap: 6px; margin: 6px 0; }
    .bnpb-chip { padding: 2px 10px; border-radius: 999px; background: #fef2f2; color: #991b1b; font-size: .75rem; font-weight: 600; }
    .bnpb-text { font-size: .88rem; color: #334155; margin: 0; }
    .bnpb-empty { display: flex; gap: 12px; align-items: center; padding: 14px; border-radius: 14px; background: #f0fdf4; color: #166534; }
</style>

<script>
(function () {
    const card = document.getElementById('bnpbCard');
    if (!card) return;
    const list = card.querySelector('[data-list]');
    const icons = { banjir: 'fa-house-flood-water', longsor: 'fa-hill-rockslide', 'cuaca ekstrem': 'fa-wind', 'puting beliung': 'fa-tornado',
        gempa: 'fa-house-crack', kebakaran: 'fa-fire', kekeringan: 'fa-sun-plant-wilt', erupsi: 'fa-volcano', abrasi: 'fa-water' };
    const icon = type => Object.entries(icons).find(([k]) => type.toLowerCase().includes(k))?.[1] || 'fa-triangle-exclamation';
    const day = d => new Date(d + 'T00:00:00').toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
    const el = (tag, cls, text) => { const e = document.createElement(tag); if (cls) e.className = cls; if (text) e.textContent = text; return e; }; // textContent only: data comes from an external API

    function render(data) {
        list.textContent = '';
        card.querySelector('[data-scope]').textContent = 'Kejadian ' + data.days + ' hari terakhir di ' + data.regions.join(', ');
        if (data.updated) card.querySelector('[data-updated]').textContent = ' · Diperbarui ' + data.updated.slice(0, 16) + ' WIB';
        if (!data.events.length) {
            const box = el('div', 'bnpb-empty');
            box.innerHTML = '<i class="fas fa-circle-check fa-lg" aria-hidden="true"></i>'; // static markup only
            box.append(el('span', '', data.updated ? 'Tidak ada kejadian bencana yang tercatat BNPB di wilayah ini dalam ' + data.days + ' hari terakhir.' : 'Data BNPB sedang tidak dapat dimuat. Silakan coba lagi nanti.'));
            list.append(box);
            return;
        }
        data.events.slice(0, 6).forEach(e => {
            const item = el('div', 'bnpb-item' + (e.region === data.regions[0] ? ' is-local' : ''));
            const ic = el('span', 'bnpb-icon'); ic.append(el('i', 'fas ' + icon(e.type))); ic.setAttribute('aria-hidden', 'true');
            const body = el('div', 'flex-grow-1');
            body.append(el('div', 'fw-bold', e.type + ' — ' + (e.region.startsWith('Kota ') ? e.region : 'Kab. ' + e.region)), el('div', 'bnpb-meta', day(e.date)));
            const chips = el('div', 'bnpb-chips');
            Object.entries(e.impact).forEach(([k, v]) => chips.append(el('span', 'bnpb-chip', v.toLocaleString('id-ID') + ' ' + k)));
            if (chips.children.length) body.append(chips);
            if (e.summary) body.append(el('p', 'bnpb-text', e.summary));
            if (e.status) body.append(el('p', 'bnpb-text text-muted mt-1', 'Kondisi: ' + e.status));
            item.append(ic, body);
            list.append(item);
        });
    }

    fetch(@json(route('disaster.show')), { headers: { 'Accept': 'application/json' } })
        .then(r => r.ok ? r.json() : Promise.reject(r.status))
        .then(render)
        .catch(() => { list.textContent = ''; list.append(el('p', 'text-muted mb-0', 'Data BNPB sedang tidak dapat dimuat. Silakan coba lagi nanti.')); });
})();
</script>
