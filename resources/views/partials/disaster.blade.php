{{-- BNPB disaster events in and around Empat Lawang (home). Filled by JS from /bencana so a slow BNPB never delays the page. --}}
<div class="bnpb-card mb-5" id="bnpbCard">
    <div class="bnpb-head">
        <span class="bnpb-head-icon" aria-hidden="true"><i class="fas fa-house-flood-water"></i></span>
        <div class="flex-grow-1">
            <h4 class="fw-bold mb-1">Informasi Bencana Sekitar Empat Lawang</h4>
            <small data-scope>Kejadian bencana tercatat BNPB di Empat Lawang dan kabupaten sekitarnya</small>
        </div>
        <a href="https://inarisk.bnpb.go.id" target="_blank" rel="noopener" class="bnpb-risk">
            <i class="fas fa-map-location-dot me-1" aria-hidden="true"></i>Cek risiko wilayah (InaRISK)
        </a>
    </div>
    <div class="bnpb-body" data-list><p class="text-muted mb-0">Memuat data bencana…</p></div>
    <small class="bnpb-foot">Sumber: BNPB (Badan Nasional Penanggulangan Bencana) &middot;
        <a href="https://gis.bnpb.go.id" target="_blank" rel="noopener">gis.bnpb.go.id</a><span data-updated></span></small>
</div>

<style>
    .bnpb-card { background: #fff; border-radius: 20px; overflow: hidden; box-shadow: 0 6px 24px rgba(15,23,42,.08); border: 1px solid #fee2e2; }
    .bnpb-head { display: flex; flex-wrap: wrap; align-items: center; gap: 14px; padding: 20px 24px; color: #fff; background: linear-gradient(135deg, #b91c1c, #7f1d1d); }
    .bnpb-head h4 { color: #fff; }
    .bnpb-head small { color: #fee2e2; }
    .bnpb-head-icon { width: 48px; height: 48px; flex: none; border-radius: 14px; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,.16); font-size: 1.4rem; color: #fde047; }
    .bnpb-risk { flex: none; padding: 6px 14px; border-radius: 999px; background: #fff; color: #b91c1c; font-size: .82rem; font-weight: 700; text-decoration: none; }
    .bnpb-risk:hover, .bnpb-risk:focus-visible { background: #fef2f2; color: #7f1d1d; }
    .bnpb-body { padding: 18px 24px 4px; display: grid; gap: 14px; }
    .bnpb-foot { display: block; padding: 10px 24px 18px; color: #64748b; }

    /* one event; --c = accent colour of the disaster type */
    .bnpb-item { --c: #475569; display: flex; gap: 14px; padding: 14px 16px; border: 1px solid #e2e8f0; border-left: 5px solid var(--c); border-radius: 14px; background: #fff; }
    .bnpb-item.is-local { background: #fff7f7; box-shadow: 0 0 0 2px #fecaca; }
    .bnpb-icon { width: 48px; height: 48px; flex: none; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; background: var(--c); font-size: 1.25rem; }
    .bnpb-title { display: flex; flex-wrap: wrap; align-items: center; gap: 6px 8px; margin-bottom: 6px; }
    .bnpb-title h5 { margin: 0; font-size: 1.05rem; font-weight: 800; color: var(--c); }
    .bnpb-badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 999px; background: #f1f5f9; color: #334155; font-size: .76rem; font-weight: 600; }
    .bnpb-badge-local { background: #b91c1c; color: #fff; }
    .bnpb-stats { display: grid; grid-template-columns: repeat(auto-fill, minmax(118px, 1fr)); gap: 8px; margin: 10px 0; }
    .bnpb-stat { display: flex; align-items: center; gap: 8px; padding: 8px 10px; border-radius: 12px; background: #f8fafc; border: 1px solid #e2e8f0; }
    .bnpb-stat i { width: 28px; height: 28px; flex: none; border-radius: 8px; display: flex; align-items: center; justify-content: center; background: #e2e8f0; color: #334155; font-size: .85rem; }
    .bnpb-stat b { display: block; font-size: 1.15rem; line-height: 1.1; color: #0f172a; }
    .bnpb-stat small { display: block; font-size: .7rem; color: #64748b; line-height: 1.2; }
    .bnpb-stat.is-severe { background: #fef2f2; border-color: #fecaca; }
    .bnpb-stat.is-severe i { background: #b91c1c; color: #fff; }
    .bnpb-stat.is-severe b { color: #b91c1c; }
    .bnpb-status { display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 999px; font-size: .8rem; font-weight: 700; background: #fef3c7; color: #92400e; }
    .bnpb-status.is-done { background: #dcfce7; color: #166534; }
    .bnpb-text { font-size: .88rem; color: #334155; margin: 8px 0 0; }
    .bnpb-text summary { cursor: pointer; list-style: none; }
    .bnpb-more { color: #0369a1; font-weight: 600; white-space: nowrap; }
    .bnpb-text summary::-webkit-details-marker { display: none; }
    .bnpb-text details[open] summary { display: none; }
    .bnpb-summary { padding: 14px; border-radius: 16px; background: #fef2f2; }
    .bnpb-sum-tiles { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; }
    .bnpb-sum { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 12px; background: #fff; box-shadow: 0 1px 3px rgba(15,23,42,.06); }
    .bnpb-sum i { width: 38px; height: 38px; flex: none; border-radius: 10px; display: flex; align-items: center; justify-content: center; background: #fee2e2; color: #b91c1c; font-size: 1rem; }
    .bnpb-sum b { display: block; font-size: 1.5rem; font-weight: 800; line-height: 1.05; color: #0f172a; }
    .bnpb-sum small { display: block; font-size: .74rem; color: #64748b; line-height: 1.2; }
    .bnpb-sum.is-severe i { background: #b91c1c; color: #fff; }
    .bnpb-sum.is-severe b { color: #b91c1c; }
    .bnpb-sum-types { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 10px; }
    .bnpb-type { --c: #475569; display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 999px; background: #fff; border: 1px solid var(--c); color: var(--c); font-size: .8rem; font-weight: 600; }
    .bnpb-type b { font-weight: 800; }
    .bnpb-meta { font-size: .8rem; color: #64748b; }
    .bnpb-empty { display: flex; gap: 12px; align-items: center; padding: 14px; border-radius: 14px; background: #f0fdf4; color: #166534; }
    @media (max-width: 767.98px) {
        .bnpb-head, .bnpb-body { padding-left: 16px; padding-right: 16px; }
        .bnpb-foot { padding-left: 16px; padding-right: 16px; }
        .bnpb-item { flex-direction: column; gap: 10px; padding: 12px; }
        .bnpb-icon { width: 40px; height: 40px; font-size: 1.05rem; }
        .bnpb-stats, .bnpb-sum-tiles { grid-template-columns: 1fr 1fr; }
        .bnpb-sum b { font-size: 1.25rem; }
    }
</style>

<script>
(function () {
    const card = document.getElementById('bnpbCard');
    if (!card) return;
    const list = card.querySelector('[data-list]');
    // disaster type keyword → [Font Awesome icon, accent colour]; first match wins
    const kinds = [
        ['banjir bandang', 'fa-water', '#1d4ed8'], ['banjir', 'fa-house-flood-water', '#0284c7'], ['longsor', 'fa-hill-rockslide', '#92400e'],
        ['puting beliung', 'fa-tornado', '#6d28d9'], ['cuaca ekstrem', 'fa-cloud-bolt', '#6d28d9'], ['gempa', 'fa-house-crack', '#b91c1c'],
        ['kebakaran', 'fa-fire', '#ea580c'], ['kekeringan', 'fa-sun-plant-wilt', '#ca8a04'], ['erupsi', 'fa-volcano', '#be123c'],
        ['gelombang', 'fa-water', '#0f766e'], ['abrasi', 'fa-water', '#0f766e'],
    ];
    const kind = type => kinds.find(([k]) => type.toLowerCase().includes(k)) || [null, 'fa-triangle-exclamation', '#475569'];
    // impact key from /bencana → [icon, label, severe]; shown in this order, zero values never reach the client
    const impacts = {
        meninggal: ['fa-heart-crack', 'meninggal', true], hilang: ['fa-person-circle-question', 'hilang', true], luka: ['fa-user-injured', 'luka-luka', true],
        mengungsi: ['fa-person-walking-luggage', 'mengungsi', false], terdampak: ['fa-users', 'jiwa terdampak', false],
        'rumah rusak': ['fa-house-crack', 'rumah rusak', false], 'rumah terendam': ['fa-house-flood-water', 'rumah terendam', false],
    };
    const day = d => new Date(d + 'T00:00:00').toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
    const el = (tag, cls, text) => { const e = document.createElement(tag); if (cls) e.className = cls; if (text) e.textContent = text; return e; }; // textContent only: data comes from an external API
    const ico = (name, cls = '') => { const i = el('i', ('fas ' + name + ' ' + cls).trim()); i.setAttribute('aria-hidden', 'true'); return i; };
    const badge = (icon, text, cls = '') => { const b = el('span', ('bnpb-badge ' + cls).trim()); b.append(ico(icon), text); return b; };
    const SHORT = 140; // chronology longer than this folds behind "Selengkapnya"

    function event(e, local) {
        const [, icon, colour] = kind(e.type);
        const item = el('article', 'bnpb-item' + (local ? ' is-local' : ''));
        item.style.setProperty('--c', colour);
        const ic = el('span', 'bnpb-icon'); ic.append(ico(icon));
        const body = el('div', 'flex-grow-1');

        const title = el('div', 'bnpb-title');
        title.append(el('h5', '', e.type), badge('fa-location-dot', e.region.startsWith('Kota ') ? e.region : 'Kab. ' + e.region, local ? 'bnpb-badge-local' : ''), badge('fa-calendar', day(e.date)));
        body.append(title);

        const stats = el('div', 'bnpb-stats');
        Object.entries(impacts).forEach(([key, [icon, label, severe]]) => {
            if (!(e.impact[key] > 0)) return;
            const s = el('div', 'bnpb-stat' + (severe ? ' is-severe' : ''));
            const t = el('div'); t.append(el('b', '', e.impact[key].toLocaleString('id-ID')), el('small', '', label));
            s.append(ico(icon), t);
            stats.append(s);
        });
        if (stats.children.length) body.append(stats);

        // "Kamis, 19 November 2026 Banjir belum surut" → badge "Banjir belum surut"
        const status = (e.status || '').replace(/^\p{L}+,\s*\d{1,2}\s+\p{L}+\s+\d{4}\s*/u, '').trim();
        if (status) {
            const done = /\b(surut|selesai|kondusif|normal|aman|padam)\b/i.test(status) && !/\b(belum|masih)\b/i.test(status);
            const s = el('span', 'bnpb-status' + (done ? ' is-done' : ''));
            s.append(ico(done ? 'fa-circle-check' : 'fa-circle-exclamation'), status);
            body.append(s);
        }

        if (e.summary) {
            const p = el('div', 'bnpb-text');
            if (e.summary.length > SHORT) {
                // closed: short text + "Selengkapnya"; open: the summary hides and the full text shows
                const cut = e.summary.lastIndexOf(' ', SHORT);
                const more = el('details'), head = el('summary');
                head.append(e.summary.slice(0, cut > 0 ? cut : SHORT) + '… ', el('span', 'bnpb-more', 'Selengkapnya'));
                more.append(head, e.summary);
                p.append(more);
            } else {
                p.textContent = e.summary;
            }
            body.append(p);
        }

        item.append(ic, body);
        return item;
    }

    function render(data) {
        list.textContent = '';
        const n = data.events.length;
        card.querySelector('[data-scope]').textContent = (n ? n + ' kejadian · ' : '') + data.days + ' hari terakhir di ' + data.regions.join(', ');
        if (data.updated) card.querySelector('[data-updated]').textContent = ' · Diperbarui ' + data.updated.slice(0, 16) + ' WIB';
        if (!n) {
            const box = el('div', 'bnpb-empty');
            box.append(ico('fa-circle-check fa-lg'), el('span', '', data.updated ? 'Tidak ada kejadian bencana yang tercatat BNPB di wilayah ini dalam ' + data.days + ' hari terakhir.' : 'Data BNPB sedang tidak dapat dimuat. Silakan coba lagi nanti.'));
            list.append(box);
            return;
        }
        list.append(summary(data.events, data.days));
        data.events.slice(0, 6).forEach(e => list.append(event(e, e.region === data.regions[0])));
        if (n > 6) list.append(el('p', 'bnpb-meta mb-0', 'Menampilkan 6 kejadian terbaru dari ' + n + ' kejadian.'));
    }

    // 30-day totals over ALL events (the list below shows only the newest 6)
    function summary(events, days) {
        const sum = keys => events.reduce((t, e) => t + keys.reduce((s, k) => s + (e.impact[k] || 0), 0), 0);
        const tiles = [
            ['fa-triangle-exclamation', events.length, 'kejadian dalam ' + days + ' hari'],
            ['fa-users', sum(['terdampak', 'mengungsi']), 'warga terdampak/mengungsi'],
            ['fa-house-chimney-crack', sum(['rumah rusak', 'rumah terendam']), 'rumah rusak/terendam'],
        ];
        const victims = sum(['meninggal', 'hilang']);
        if (victims) tiles.push(['fa-heart-crack', victims, 'meninggal/hilang', true]);

        const box = el('div', 'bnpb-summary');
        const row = el('div', 'bnpb-sum-tiles');
        tiles.forEach(([icon, value, label, severe]) => {
            const tile = el('div', 'bnpb-sum' + (severe ? ' is-severe' : ''));
            const t = el('div'); t.append(el('b', '', value.toLocaleString('id-ID')), el('small', '', label));
            tile.append(ico(icon), t);
            row.append(tile);
        });
        const types = el('div', 'bnpb-sum-types');
        const count = {};
        events.forEach(e => { count[e.type] = (count[e.type] || 0) + 1; });
        Object.entries(count).sort((a, b) => b[1] - a[1]).forEach(([type, c]) => {
            const [, icon, colour] = kind(type);
            const chip = el('span', 'bnpb-type');
            chip.style.setProperty('--c', colour);
            chip.append(ico(icon), type + ' ', el('b', '', String(c)));
            types.append(chip);
        });
        box.append(row, types);
        return box;
    }

    fetch(@json(route('disaster.show')), { headers: { 'Accept': 'application/json' } })
        .then(r => r.ok ? r.json() : Promise.reject(r.status))
        .then(render)
        .catch(() => { list.textContent = ''; list.append(el('p', 'text-muted mb-0', 'Data BNPB sedang tidak dapat dimuat. Silakan coba lagi nanti.')); });
})();
</script>
