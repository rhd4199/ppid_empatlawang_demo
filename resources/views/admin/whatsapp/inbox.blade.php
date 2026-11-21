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
@php $initial = fn ($c) => mb_strtoupper(mb_substr($c->displayName() ?: 'W', 0, 1)); @endphp
<div class="wa-app">
    <div class="row g-0">
        {{-- Contacts; on phones hidden while a conversation is open. --}}
        <div class="col-lg-4 border-end h-100 flex-column {{ $conversation ? 'd-none d-lg-flex' : 'd-flex' }}">
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
        <div class="col-lg-8 h-100 flex-column {{ $conversation ? 'd-flex' : 'd-none d-lg-flex' }}">
            @foreach (['success' => 'success', 'error' => 'danger'] as $key => $class)
                @if (session($key))
                    <div class="alert alert-{{ $class }} border-0 rounded-0 py-2 mb-0 small">{{ session($key) }}</div>
                @endif
            @endforeach
            @error('body')<div class="alert alert-danger border-0 rounded-0 py-2 mb-0 small">{{ $message }}</div>@enderror

            @if ($conversation)
                <div class="wa-pane-head d-flex align-items-center gap-3 px-3">
                    <a href="{{ route('admin.wa-inbox.index', $filters) }}" class="btn btn-sm btn-link text-secondary d-lg-none px-1" aria-label="Kembali"><i class="fas fa-arrow-left"></i></a>
                    <div class="wa-avatar sm">{{ $initial($conversation) }}</div>
                    <div style="min-width:0">
                        <div class="fw-semibold text-truncate">{{ $conversation->displayName() ?: 'Warga' }}</div>
                        <div class="small text-muted text-truncate">+{{ $conversation->phone }} · {{ App\Models\WaConversation::STATUSES[$conversation->status] ?? $conversation->status }}@if ($conversation->label) · {{ App\Models\WaConversation::LABELS[$conversation->label] ?? $conversation->label }}@endif</div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle ms-auto" data-bs-toggle="collapse" data-bs-target="#waContact" aria-expanded="{{ $errors->hasAny(['contact_name', 'label', 'notes']) ? 'true' : 'false' }}" title="Info kontak" aria-label="Info kontak"><i class="fas fa-user-pen"></i></button>
                    <a href="{{ wa_link($conversation->phone) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-success rounded-circle" title="Buka di WhatsApp"><i class="fab fa-whatsapp"></i></a>
                </div>
                {{-- Contact card (CRM): officer-set name, label, notes; WhatsApp profile name stays as a hint. --}}
                <form id="waContact" action="{{ route('admin.wa-inbox.contact', [$conversation] + $filters) }}" method="POST"
                      class="collapse border-bottom bg-light px-3 py-2 {{ $errors->hasAny(['contact_name', 'label', 'notes']) ? 'show' : '' }}" data-wa-contact>
                    @csrf
                    @method('PUT')
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label small mb-0" for="waContactName">Nama kontak</label>
                            <input id="waContactName" name="contact_name" maxlength="100" class="form-control form-control-sm" value="{{ old('contact_name', $conversation->contact_name) }}" placeholder="{{ $conversation->name ? 'Nama WA: ' . $conversation->name : 'mis. Budi Santoso' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small mb-0" for="waContactLabel">Label</label>
                            <select id="waContactLabel" name="label" class="form-select form-select-sm">
                                <option value="">— Tanpa label —</option>
                                @foreach (App\Models\WaConversation::LABELS as $key => $text)
                                    <option value="{{ $key }}" @selected(old('label', $conversation->label) === $key)>{{ $text }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small mb-0" for="waContactNotes">Catatan petugas</label>
                            <textarea id="waContactNotes" name="notes" rows="2" maxlength="2000" class="form-control form-control-sm" placeholder="mis. minta salinan Laporan Kinerja 2025, sudah dikirim via email">{{ old('notes', $conversation->notes) }}</textarea>
                        </div>
                        @foreach (['contact_name', 'label', 'notes'] as $field)
                            @error($field)<div class="col-12 small text-danger">{{ $message }}</div>@enderror
                        @endforeach
                        <div class="col-12 text-end"><button type="submit" class="btn btn-sm btn-success rounded-pill"><i class="fas fa-save me-1"></i> Simpan</button></div>
                    </div>
                </form>
                <div class="wa-thread flex-grow-1 px-3 px-md-5 py-3" data-wa-thread data-wa-poll="{{ route('admin.wa-inbox.messages', $conversation) }}" data-wa-after="{{ $messages->max('id') ?? 0 }}" data-wa-since="{{ now()->toDateTimeString() }}">
                    <div class="text-center mb-2"><span class="wa-day">Pertanyaan warga · diteruskan dari bot WhatsApp{{ $conversation->handoff_reason ? ' · ' . $conversation->handoff_reason : '' }}</span></div>
                    @php $lastDay = null; @endphp
                    @foreach ($messages as $m)
                        @php $day = App\Models\WaMessage::dayLabel($m->sent_at); @endphp
                        @if ($day !== $lastDay)
                            <div class="text-center my-2" data-wa-day><span class="wa-day">{{ $day }}</span></div>
                            @php $lastDay = $day; @endphp
                        @endif
                        @include('admin.whatsapp._message')
                    @endforeach
                </div>
                <div class="wa-composer px-3 py-2">
                    <form id="redraftForm" action="{{ route('admin.wa-inbox.redraft', $conversation) }}" method="POST">@csrf</form>
                    @if (filled($conversation->reply_draft))
                        {{-- AI suggestion: sent only by an officer ("Setujui & kirim"), or copied into the input to edit. --}}
                        <form action="{{ route('admin.wa-inbox.send', $conversation) }}" method="POST" class="wa-suggest bg-white rounded-3 shadow-sm p-2 mb-2" data-wa-suggest>
                            @csrf
                            <input type="hidden" name="body" value="{{ $conversation->reply_draft }}">
                            <div class="small fw-bold mb-1"><i class="fas fa-robot text-primary me-1"></i>Saran balasan Sapa Madani (AI) — periksa sebelum dikirim</div>
                            <div class="wa-suggest-text small" data-wa-suggest-text>{{ $conversation->reply_draft }}</div>
                            <div class="d-flex flex-wrap gap-2 justify-content-end mt-2">
                                <button type="submit" form="redraftForm" class="btn btn-sm btn-light rounded-pill"><i class="fas fa-rotate me-1"></i> Buat ulang</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" data-wa-use><i class="fas fa-pen me-1"></i> Pakai/Ubah</button>
                                <button type="submit" class="btn btn-sm btn-success rounded-pill fw-bold"><i class="fas fa-paper-plane me-1"></i> Setujui &amp; kirim</button>
                            </div>
                        </form>
                    @endif
                    {{-- Free-text reply bar: Enter sends, Shift+Enter adds a line. --}}
                    <form action="{{ route('admin.wa-inbox.send', $conversation) }}" method="POST" class="d-flex align-items-end gap-2" data-wa-compose>
                        @csrf
                        @if (blank($conversation->reply_draft))
                            <button type="submit" form="redraftForm" class="btn btn-link text-secondary px-1" title="Buat saran balasan AI" aria-label="Buat saran balasan AI"><i class="fas fa-robot"></i></button>
                        @endif
                        <textarea id="waInput" name="body" rows="1" maxlength="4000" required class="form-control border-0 rounded-4 wa-input" placeholder="Ketik pesan" aria-label="Ketik pesan">{{ old('body') }}</textarea>
                        <button type="submit" class="btn btn-success rounded-circle wa-send flex-shrink-0" title="Kirim" aria-label="Kirim"><i class="fas fa-paper-plane"></i></button>
                    </form>
                </div>
            @else
                <div class="flex-grow-1 d-flex align-items-center justify-content-center text-muted wa-thread">
                    <div class="text-center bg-white rounded-4 shadow-sm px-4 py-3"><i class="fab fa-whatsapp fa-3x mb-2 text-success"></i><div>Pilih percakapan untuk membaca pesan.</div></div>
                </div>
            @endif
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
    // Newest message in view, like a chat app.
    document.querySelectorAll('.wa-thread').forEach(el => { el.scrollTop = el.scrollHeight; });
    const waInput = document.getElementById('waInput');
    const waGrow = () => { waInput.style.height = 'auto'; waInput.style.height = waInput.scrollHeight + 'px'; };
    waInput?.addEventListener('input', waGrow);
    waInput?.addEventListener('keydown', e => {
        if (e.key === 'Enter' && !e.shiftKey && !e.isComposing) {
            e.preventDefault();
            if (waInput.value.trim() !== '') waInput.form.requestSubmit();
        }
    });
    document.querySelector('[data-wa-use]')?.addEventListener('click', () => {
        waInput.value = document.querySelector('[data-wa-suggest] input[name=body]').value;
        waGrow();
        waInput.focus();
    });
    // One submit per form: no double message on a double click/Enter.
    document.querySelectorAll('[data-wa-compose], [data-wa-suggest]').forEach(f => f.addEventListener('submit', () => {
        f.querySelectorAll('button[type=submit]').forEach(b => { b.disabled = true; });
    }));
    // Live updates (poll ~5 s, like WhatsApp): new messages + ticks in the open chat, contact list, sidebar badge.
    // The reply input and suggestion card are never touched, so typed text is kept.
    (() => {
        const thread = document.querySelector('[data-wa-thread]');
        const list = document.querySelector('[data-wa-list]');
        const getJson = url => fetch(url, { headers: { Accept: 'application/json' }, credentials: 'same-origin' })
            .then(r => { if (!r.ok) throw new Error(r.status); return r.json(); });
        const toNode = html => { const t = document.createElement('template'); t.innerHTML = html.trim(); return t.content.firstElementChild; };
        const pollThread = () => {
            const url = new URL(thread.dataset.waPoll, location.href);
            url.searchParams.set('after', thread.dataset.waAfter);
            url.searchParams.set('since', thread.dataset.waSince);
            return getJson(url).then(d => {
                const atBottom = thread.scrollHeight - thread.scrollTop - thread.clientHeight < 80;
                d.changed.forEach(m => thread.querySelector(`[data-wa-msg="${m.id}"]`)?.replaceWith(toNode(m.html)));
                d.messages.forEach(m => {
                    if (thread.querySelector(`[data-wa-msg="${m.id}"]`)) return;
                    const last = [...thread.querySelectorAll('[data-wa-msg]')].pop();
                    if (!last || last.dataset.waMsgDay !== m.day) {
                        const day = document.createElement('div'), label = document.createElement('span');
                        day.className = 'text-center my-2';
                        day.dataset.waDay = '';
                        label.className = 'wa-day';
                        label.textContent = m.day;
                        day.append(label);
                        thread.append(day);
                    }
                    thread.append(toNode(m.html));
                    thread.dataset.waAfter = Math.max(+thread.dataset.waAfter, m.id);
                });
                thread.dataset.waSince = d.since;
                if (d.messages.length && atBottom) thread.scrollTop = thread.scrollHeight;
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
        const tick = () => {
            if (busy || document.hidden) return;
            busy = true;
            // Thread first: it marks the open chat read, so the list badge right after is correct.
            Promise.resolve(thread && pollThread()).then(() => list && pollList()).catch(() => {}).finally(() => { busy = false; });
        };
        if (thread || list) setInterval(tick, 5000);
    })();
</script>
@endpush
