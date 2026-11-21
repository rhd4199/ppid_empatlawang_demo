@extends('layouts.admin')

@section('title', 'Kotak Masuk WhatsApp')

@push('styles')
<style>
    /* Full height under the sticky header (in flow): cancel <main>'s p-4 padding (layouts/admin; p-4 is !important, so its inline padding-top does not apply). */
    .wa-app { margin: -1.5rem; height: calc(100vh - var(--header-height)); min-height: 420px; background: #fff; }
    .wa-app > .row { height: 100%; }
    .wa-pane-head { background: #f0f2f5; min-height: 60px; }
    .wa-list, .wa-thread { overflow-y: auto; }
    .wa-avatar { width: 46px; height: 46px; flex: 0 0 46px; border-radius: 50%; background: #25d366; color: #fff;
        display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.1rem; }
    .wa-avatar.sm { width: 40px; height: 40px; flex-basis: 40px; }
    .wa-item { color: inherit; text-decoration: none; border-bottom: 1px solid #f0f2f5; }
    .wa-item:hover { background: #f5f6f6; color: inherit; }
    .wa-item.active { background: #e9edef; }
    .wa-item .wa-time { font-size: .75rem; color: #667781; }
    .wa-item.unread .wa-time { color: #25d366; font-weight: 600; }
    .wa-count { min-width: 20px; height: 20px; padding: 0 6px; border-radius: 10px; background: #25d366; color: #fff;
        font-size: .72rem; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; }
    .wa-chip { background: #f0f2f5; color: #54656f; }
    .wa-chip.active { background: #d9fdd3; color: #008069; font-weight: 600; }
    .wa-tag { background: #d9fdd3; color: #008069; font-weight: 600; }
    /* WhatsApp wallpaper: beige with a faint dot pattern */
    .wa-thread { background-color: #efeae2;
        background-image: radial-gradient(rgba(0,0,0,.045) 1px, transparent 1px), radial-gradient(rgba(0,0,0,.03) 1px, transparent 1px);
        background-size: 22px 22px, 22px 22px; background-position: 0 0, 11px 11px; }
    .wa-day { display: inline-block; background: #fff; color: #54656f; font-size: .75rem; padding: .25rem .75rem; border-radius: .5rem; box-shadow: 0 1px .5px rgba(11,20,26,.13); }
    .wa-bubble { position: relative; max-width: 72%; padding: .4rem .6rem .3rem; border-radius: .5rem; overflow-wrap: anywhere;
        box-shadow: 0 1px .5px rgba(11,20,26,.13); font-size: .92rem; }
    .wa-text { white-space: pre-wrap; }
    /* bubble tail */
    .wa-bubble::before { content: ''; position: absolute; top: 0; width: 10px; height: 13px; }
    .wa-in { background: #fff; border-top-left-radius: 0; margin-left: 8px; }
    .wa-in::before { left: -8px; background: linear-gradient(225deg, #fff 50%, transparent 50%); }
    .wa-out { background: #d9fdd3; border-top-right-radius: 0; margin-left: auto; margin-right: 8px; }
    .wa-out::before { right: -8px; background: linear-gradient(135deg, #d9fdd3 50%, transparent 50%); }
    .wa-meta { float: right; margin: .35rem 0 -.1rem .6rem; font-size: .68rem; color: #667781; white-space: nowrap; }
    .wa-tick { margin-left: .2rem; }
    .wa-tick-read { color: #53bdeb; }
    .wa-tick-failed { color: #dc3545; }
    .wa-composer { background: #f0f2f5; }
    .wa-suggest { border-left: 4px solid #25d366; }
    .wa-suggest-text { white-space: pre-wrap; overflow-wrap: anywhere; max-height: 30vh; overflow-y: auto; }
    .wa-input { resize: none; max-height: 140px; padding: .55rem .9rem; }
    .wa-send { width: 42px; height: 42px; display: inline-flex; align-items: center; justify-content: center; background: #00a884; border-color: #00a884; }
    @media (max-width: 991.98px) { .wa-bubble { max-width: 85%; } }
</style>
@endpush

@section('content')
<div class="wa-app">
    <div class="row g-0">
        {{-- Contacts; on phones hidden while a conversation is open. --}}
        <div class="col-lg-4 border-end h-100 flex-column {{ $conversation ? 'd-none d-lg-flex' : 'd-flex' }}" data-wa-side>
            <div class="wa-pane-head d-flex align-items-center justify-content-between px-3">
                <div class="fw-bold"><i class="fab fa-whatsapp text-success me-2"></i>Kotak masuk</div>
                <div class="d-flex align-items-center gap-2">
                    <span class="small text-muted">{{ $conversations->total() }} percakapan</span>
                    <button type="button" class="btn btn-sm btn-success rounded-pill" data-bs-toggle="modal" data-bs-target="#waNewChat" title="Kirim pesan ke nomor baru"><i class="fas fa-plus me-1"></i>Chat baru</button>
                    <a href="{{ route('admin.whatsapp.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill" title="Pengaturan WhatsApp (API key, provider, bot)"><i class="fas fa-gear me-1"></i>Pengaturan</a>
                </div>
            </div>
            @php
                $filters = App\Http\Controllers\Admin\WhatsAppInboxController::filters();
                $chips = ['' => 'Semua', 'new' => 'Baru', 'handoff' => 'Butuh petugas', 'done' => 'Selesai'];
            @endphp
            {{-- Search + quick filters, like WhatsApp; GET so the URL can be shared. --}}
            <form action="{{ route('admin.wa-inbox.index') }}" method="GET" class="px-3 py-2 border-bottom" role="search" data-wa-search>
                @if (! empty($filters['status']))<input type="hidden" name="status" value="{{ $filters['status'] }}">@endif
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-0 rounded-start-pill"><i class="fas fa-magnifying-glass text-muted"></i></span>
                    <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="100" class="form-control bg-light border-0 rounded-end-pill" placeholder="Cari nama, nomor, catatan atau pesan" aria-label="Cari percakapan">
                </div>
                <div class="d-flex flex-wrap gap-1 mt-2">
                    @foreach ($chips as $key => $label)
                        <a href="{{ route('admin.wa-inbox.index', array_filter(['q' => $filters['q'] ?? null, 'status' => $key ?: null])) }}"
                           class="badge rounded-pill text-decoration-none fw-normal px-3 py-2 wa-chip {{ ($filters['status'] ?? '') === $key ? 'active' : '' }}">{{ $label }}</a>
                    @endforeach
                </div>
            </form>
            <div class="wa-list flex-grow-1" data-wa-list data-wa-poll="{{ route('admin.wa-inbox.summary', array_filter(['current' => $conversation?->id] + request()->only('page', 'q', 'status'))) }}">
                @include('admin.whatsapp._list', ['currentId' => $conversation?->id])
            </div>
            @if ($conversations->hasPages())
                <div class="p-2 border-top">{{ $conversations->links() }}</div>
            @endif
        </div>

        {{-- Chat; on phones shown only when one is open. --}}
        <div class="col-lg-8 h-100 flex-column {{ $conversation ? 'd-flex' : 'd-none d-lg-flex' }}" data-wa-pane>
            @include('admin.whatsapp._thread')
        </div>
    </div>
</div>

{{-- Officer starts a chat; a number that already has a conversation reuses it. --}}
<div class="modal fade" id="waNewChat" tabindex="-1" aria-labelledby="waNewChatTitle" aria-hidden="true" data-wa-new-open="{{ $errors->hasAny(['new_phone', 'new_name', 'new_body']) ? '1' : '' }}">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.wa-inbox.start') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="waNewChatTitle"><i class="fab fa-whatsapp text-success me-2"></i>Chat baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="waNewPhone" class="form-label small">Nomor WhatsApp</label>
                    <input id="waNewPhone" name="new_phone" type="tel" inputmode="tel" maxlength="30" required value="{{ old('new_phone') }}" class="form-control @error('new_phone') is-invalid @enderror" placeholder="mis. 081234567890">
                    @error('new_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="waNewName" class="form-label small">Nama kontak <span class="text-muted">(opsional)</span></label>
                    <input id="waNewName" name="new_name" maxlength="100" value="{{ old('new_name') }}" class="form-control @error('new_name') is-invalid @enderror" placeholder="mis. Ibu Rina">
                    @error('new_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label for="waNewBody" class="form-label small">Pesan pertama</label>
                    <textarea id="waNewBody" name="new_body" rows="4" maxlength="4000" required class="form-control @error('new_body') is-invalid @enderror">{{ old('new_body') }}</textarea>
                    @error('new_body')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-success"><i class="fas fa-paper-plane me-1"></i>Kirim</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Validation failed: show the "Chat baru" form again with the error.
    const waNewChat = document.getElementById('waNewChat');
    if (waNewChat?.dataset.waNewOpen) bootstrap.Modal.getOrCreateInstance(waNewChat).show();
    // The right pane is swapped in-page when switching chats, so everything in it is looked up on use
    // (delegated events), never kept from page load.
    (() => {
        const pane = document.querySelector('[data-wa-pane]'), side = document.querySelector('[data-wa-side]');
        const list = document.querySelector('[data-wa-list]');
        const thread = () => pane.querySelector('[data-wa-thread]');
        const input = () => document.getElementById('waInput');
        const grow = el => { el.style.height = 'auto'; el.style.height = el.scrollHeight + 'px'; };
        const toBottom = () => { const t = thread(); if (t) t.scrollTop = t.scrollHeight; };
        const getJson = url => fetch(url, { headers: { Accept: 'application/json' }, credentials: 'same-origin' })
            .then(r => { if (!r.ok) throw new Error(r.status); return r.json(); });
        const toNode = html => { const t = document.createElement('template'); t.innerHTML = html.trim(); return t.content.firstElementChild; };
        // Append a bubble, with a day separator when the day changes.
        const appendMsg = (th, node, dayLabel) => {
            const last = [...th.querySelectorAll('[data-wa-msg]')].pop();
            if (!last || last.dataset.waMsgDay !== dayLabel) {
                const day = document.createElement('div'), label = document.createElement('span');
                day.className = 'text-center my-2';
                day.dataset.waDay = '';
                label.className = 'wa-day';
                label.textContent = dayLabel;
                day.append(label);
                th.append(day);
            }
            th.append(node);
        };
        toBottom(); // newest message in view, like a chat app

        // AI draft in the background (redraft as JSON), so opening a chat never waits for the AI.
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
        let drafting = null;
        const loadDraft = (url, card) => {
            if (drafting === url) return; // robot button / "Buat ulang" clicked again
            drafting = url;
            const compose = pane.querySelector('[data-wa-compose]'), text = card?.querySelector('[data-wa-suggest-text]');
            const before = text && [text.className, text.innerHTML];
            if (text) {
                text.className = 'wa-suggest-text small text-muted';
                text.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Menyiapkan draf…';
                card.querySelectorAll('button').forEach(b => { b.disabled = true; });
            }
            fetch(url, { method: 'POST', credentials: 'same-origin', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf } })
                .then(r => { if (!r.ok) throw new Error(r.status); return r.json(); })
                .then(d => {
                    if (!compose?.isConnected) return; // another chat was opened meanwhile
                    const node = d.html.trim() ? toNode(d.html) : null;
                    if (card?.isConnected) node ? card.replaceWith(node) : card.remove();
                    else if (node) compose.before(node);
                })
                .catch(() => {
                    if (!card?.isConnected) return;
                    card.querySelectorAll('button').forEach(b => { b.disabled = false; });
                    if (card.dataset.waDraftLoad) text.textContent = 'Draf gagal dibuat. Klik ikon robot untuk mencoba lagi.';
                    else [text.className, text.innerHTML] = before; // keep the previous draft
                })
                .finally(() => { drafting = null; });
        };
        const autoDraft = () => { const card = pane.querySelector('[data-wa-draft-load]'); if (card) loadDraft(card.dataset.waDraftLoad, card); };
        autoDraft();

        // Reply bar: Enter sends, Shift+Enter adds a line; "Pakai/Ubah" copies the AI draft into it.
        pane.addEventListener('input', e => { if (e.target.id === 'waInput') grow(e.target); });
        pane.addEventListener('keydown', e => {
            if (e.target.id !== 'waInput' || e.key !== 'Enter' || e.shiftKey || e.isComposing) return;
            e.preventDefault();
            if (e.target.value.trim() !== '') e.target.form.requestSubmit();
        });
        pane.addEventListener('click', e => {
            if (!e.target.closest('[data-wa-use]')) return;
            const el = input();
            el.value = pane.querySelector('[data-wa-suggest] input[name=body]').value;
            grow(el);
            el.focus();
        });

        // Send without reloading: a pending bubble shows at once; the stored one (sent/failed) replaces it.
        // waAfter is left to the poll, so citizen messages that arrive meanwhile are not skipped.
        pane.addEventListener('submit', e => {
            if (e.target.id === 'redraftForm') {
                e.preventDefault();
                return loadDraft(e.target.action, pane.querySelector('[data-wa-suggest]'));
            }
            const f =e.target.closest('[data-wa-compose], [data-wa-suggest]'), th = thread();
            if (!f || !th) return;
            e.preventDefault();
            const data = new FormData(f), text = String(data.get('body') || '').trim(), el = input();
            if (f.dataset.waBusy || !text) return; // Enter twice / double click: one message
            f.dataset.waBusy = '1';
            if (f.matches('[data-wa-compose]')) { el.value = ''; grow(el); }
            const restore = () => { if (el.isConnected && !el.value) { el.value = text; grow(el); } };
            const temp = pane.querySelector('[data-wa-pending]').content.firstElementChild.cloneNode(true);
            temp.querySelector('.wa-text').textContent = text;
            temp.querySelector('[data-wa-time]').textContent = new Date().toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit', timeZone: 'Asia/Jakarta' });
            appendMsg(th, temp, 'Hari ini');
            th.scrollTop = th.scrollHeight;
            fetch(f.action, { method: 'POST', body: data, credentials: 'same-origin', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': data.get('_token') } })
                .then(r => r.json().catch(() => ({})).then(d => { if (!r.ok) throw new Error(d.message || 'HTTP ' + r.status); return d; }))
                .then(d => {
                    th.querySelector(`[data-wa-msg="${d.id}"]`) ? temp.remove() : temp.replaceWith(toNode(d.html));
                    if (!d.ok) return restore(); // failed bubble stays, text back for a retry
                    if (f.matches('[data-wa-suggest]')) f.remove(); // draft sent and cleared
                })
                .catch(err => { temp.remove(); restore(); alert('Pesan gagal dikirim: ' + err.message); })
                .finally(() => { delete f.dataset.waBusy; });
        });

        // Switch chats without a page load: fetch only the right pane (X-WA-Pane), keep the URL shareable.
        const chatId = url => (String(url).match(/kotak-masuk-wa\/(\d+)/) || [])[1];
        const markActive = url => {
            const id = chatId(url);
            list.querySelectorAll('.wa-item').forEach(a => a.classList.toggle('active', chatId(a.href) === id));
            const poll = new URL(list.dataset.waPoll, location.href);
            poll.searchParams.set('current', id);
            list.dataset.waPoll = poll.pathname + poll.search;
        };
        let loading = 0;
        const openChat = (url, push) => {
            const seq = ++loading;
            fetch(url, { headers: { 'X-WA-Pane': '1', Accept: 'text/html' }, credentials: 'same-origin' })
                .then(r => { if (!r.ok || r.redirected) throw new Error(r.status); return r.text(); })
                .then(html => {
                    if (seq !== loading) return; // a later click won
                    pane.innerHTML = html;
                    // Phones: chat replaces the list, like opening the page directly.
                    pane.classList.replace('d-none', 'd-flex');
                    pane.classList.remove('d-lg-flex');
                    side.classList.replace('d-flex', 'd-none');
                    side.classList.add('d-lg-flex');
                    if (push) history.pushState({ waPane: 1 }, '', url);
                    markActive(url);
                    toBottom();
                    autoDraft();
                    tick(true);
                })
                .catch(() => { location.href = url; }); // session expired / error: normal navigation
        };
        list?.addEventListener('click', e => {
            const a = e.target.closest('a.wa-item');
            if (!a || e.ctrlKey || e.metaKey || e.shiftKey || e.altKey || e.button !== 0) return;
            e.preventDefault();
            openChat(a.href, true);
        });
        window.addEventListener('popstate', () => {
            chatId(location.pathname) ? openChat(location.href, false) : location.reload();
        });

        // Live updates (poll ~5 s, like WhatsApp): new messages + ticks in the open chat, contact list, sidebar badge.
        // The reply input and suggestion card are never touched, so typed text is kept.
        const pollThread = th => {
            const url = new URL(th.dataset.waPoll, location.href);
            url.searchParams.set('after', th.dataset.waAfter);
            url.searchParams.set('since', th.dataset.waSince);
            return getJson(url).then(d => {
                const atBottom = th.scrollHeight - th.scrollTop - th.clientHeight < 80;
                d.changed.forEach(m => th.querySelector(`[data-wa-msg="${m.id}"]`)?.replaceWith(toNode(m.html)));
                d.messages.forEach(m => {
                    // Already shown (a reply sent from this page): refresh it, its tick may have moved.
                    const shown = th.querySelector(`[data-wa-msg="${m.id}"]`);
                    shown ? shown.replaceWith(toNode(m.html)) : appendMsg(th, toNode(m.html), m.day);
                    th.dataset.waAfter = Math.max(+th.dataset.waAfter, m.id);
                });
                th.dataset.waSince = d.since;
                if (d.messages.length && atBottom) th.scrollTop = th.scrollHeight;
            });
        };
        const pollList = () => getJson(list.dataset.waPoll).then(d => {
            list.innerHTML = d.list;
            const link = document.querySelector('.menu-item[href$="/kotak-masuk-wa"]');
            let badge = link?.querySelector('.badge');
            if (d.unread && !badge && link) {
                badge = document.createElement('span');
                badge.className = 'badge rounded-pill bg-success ms-auto';
                badge.dataset.waUnread = '';
                link.append(badge);
            }
            if (badge) d.unread ? (badge.textContent = d.unread) : badge.remove();
        });
        let busy = false;
        // listOnly: right after a switch the pane is fresh, only the list (read status, badge) needs refreshing.
        const tick = listOnly => {
            if (busy || document.hidden) return;
            busy = true;
            const th = listOnly === true ? null : thread();
            // Thread first: it marks the open chat read, so the list badge right after is correct.
            Promise.resolve(th && pollThread(th)).then(() => list && pollList()).catch(() => {}).finally(() => { busy = false; });
        };
        setInterval(tick, 5000);
    })();
</script>
@endpush
