@extends('layouts.admin')

@section('title', 'Kotak Masuk WhatsApp')

@push('styles')
<style>
    /* Full height under the sticky header: cancel <main>'s padding (layouts/admin). */
    .wa-app { margin: calc(-1 * (var(--header-height) + 1rem)) -1.5rem -1.5rem; height: calc(100vh - var(--header-height)); min-height: 420px; background: #fff; }
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
@php
    // Day separator label (WIB), like WhatsApp: Hari ini / Kemarin / 21 November 2026.
    $dayLabel = function ($date) {
        $date = $date->copy()->timezone('Asia/Jakarta');
        $today = now('Asia/Jakarta');

        return $date->isSameDay($today) ? 'Hari ini' : ($date->isSameDay($today->copy()->subDay()) ? 'Kemarin' : $date->locale('id')->translatedFormat('j F Y'));
    };
    // Contact list time: 14:05 today, "Kemarin", else 19/11/26.
    $listTime = function ($date) use ($dayLabel) {
        if (! $date) {
            return '';
        }
        $label = $dayLabel($date);
        $local = $date->copy()->timezone('Asia/Jakarta');

        return $label === 'Hari ini' ? $local->format('H:i') : ($label === 'Kemarin' ? 'Kemarin' : $local->format('d/m/y'));
    };
    // Outgoing status → WhatsApp tick (pending clock, sent ✓, delivered ✓✓ grey, read ✓✓ blue, failed !).
    $ticks = [
        'pending' => ['fa-regular fa-clock', 'Menunggu'],
        'sent' => ['fas fa-check', 'Terkirim'],
        'delivered' => ['fas fa-check-double', 'Diterima'],
        'read' => ['fas fa-check-double', 'Dibaca'],
        'failed' => ['fas fa-circle-exclamation', 'Gagal terkirim'],
    ];
    $initial = fn ($c) => mb_strtoupper(mb_substr($c->name ?: 'W', 0, 1));
@endphp
<div class="wa-app">
    <div class="row g-0">
        {{-- Contacts; on phones hidden while a conversation is open. --}}
        <div class="col-lg-4 border-end h-100 flex-column {{ $conversation ? 'd-none d-lg-flex' : 'd-flex' }}">
            <div class="wa-pane-head d-flex align-items-center justify-content-between px-3">
                <div class="fw-bold"><i class="fab fa-whatsapp text-success me-2"></i>Kotak masuk</div>
                <span class="small text-muted">{{ $conversations->total() }} percakapan</span>
            </div>
            <div class="wa-list flex-grow-1">
                @forelse ($conversations as $c)
                    @php $unread = $c->status === 'new' ? max(1, (int) $c->unread_count) : 0; @endphp
                    <a href="{{ route('admin.wa-inbox.show', $c) }}" class="wa-item d-flex gap-3 px-3 py-2 align-items-center {{ $conversation?->id === $c->id ? 'active' : '' }} {{ $unread ? 'unread' : '' }}">
                        <div class="wa-avatar">{{ $initial($c) }}</div>
                        <div class="flex-grow-1" style="min-width:0">
                            <div class="d-flex justify-content-between gap-2">
                                <div class="fw-semibold text-truncate">{{ $c->name ?: '+' . $c->phone }}</div>
                                <div class="wa-time flex-shrink-0">{{ $listTime($c->last_message_at) }}</div>
                            </div>
                            <div class="small text-muted text-truncate">
                                <span class="text-success">Warga · WhatsApp</span>@if ($c->name) · +{{ $c->phone }}@endif
                                @if ($c->status === 'new')<span class="badge rounded-pill wa-tag ms-1">Baru</span>@endif
                            </div>
                            <div class="d-flex justify-content-between align-items-center gap-2">
                                <div class="small text-muted text-truncate">{{ Str::limit($c->last_body, 80) }}</div>
                                @if ($unread)<span class="wa-count flex-shrink-0" title="Belum dibaca">{{ $unread }}</span>@endif
                            </div>
                            @if ($c->status !== 'done' && $c->handoff_reason)<div class="small text-warning-emphasis text-truncate"><i class="fas fa-user-tie me-1"></i>Butuh petugas: {{ $c->handoff_reason }}</div>@endif
                        </div>
                    </a>
                @empty
                    <div class="p-4 text-center text-muted small">Belum ada pesan WhatsApp masuk.</div>
                @endforelse
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
                    <a href="{{ route('admin.wa-inbox.index') }}" class="btn btn-sm btn-link text-secondary d-lg-none px-1" aria-label="Kembali"><i class="fas fa-arrow-left"></i></a>
                    <div class="wa-avatar sm">{{ $initial($conversation) }}</div>
                    <div style="min-width:0">
                        <div class="fw-semibold text-truncate">{{ $conversation->name ?: 'Warga' }}</div>
                        <div class="small text-muted text-truncate">+{{ $conversation->phone }} · {{ App\Models\WaConversation::STATUSES[$conversation->status] ?? $conversation->status }}</div>
                    </div>
                    <a href="{{ wa_link($conversation->phone) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-success rounded-circle ms-auto" title="Buka di WhatsApp"><i class="fab fa-whatsapp"></i></a>
                </div>
                <div class="wa-thread flex-grow-1 px-3 px-md-5 py-3">
                    <div class="text-center mb-2"><span class="wa-day">Pertanyaan warga · diteruskan dari bot WhatsApp{{ $conversation->handoff_reason ? ' · ' . $conversation->handoff_reason : '' }}</span></div>
                    @php $lastDay = null; @endphp
                    @foreach ($messages as $m)
                        @php $day = $dayLabel($m->sent_at); @endphp
                        @if ($day !== $lastDay)
                            <div class="text-center my-2" data-wa-day><span class="wa-day">{{ $day }}</span></div>
                            @php $lastDay = $day; @endphp
                        @endif
                        <div class="d-flex mb-2">
                            <div class="wa-bubble {{ $m->direction === 'out' ? 'wa-out' : 'wa-in' }}">
                                @if ($m->direction === 'out')<div class="small fw-bold" style="color:#008069">{{ $m->sender === 'bot' ? 'Bot Sapa Madani (otomatis)' : 'Petugas PPID' }}</div>@endif
                                <span class="wa-text">{{ $m->body }}</span><span class="wa-meta">{{ $m->sent_at->timezone('Asia/Jakarta')->format('H:i') }}@if ($m->direction === 'out' && isset($ticks[$m->status]))<i class="wa-tick wa-tick-{{ $m->status }} {{ $ticks[$m->status][0] }}" title="{{ $ticks[$m->status][1] }}" aria-label="{{ $ticks[$m->status][1] }}"></i>@endif</span>
                                @if ($m->status === 'failed')<div class="small text-danger mt-1" style="clear:both">Gagal terkirim{{ $m->error ? ': ' . $m->error : '' }}</div>@endif
                            </div>
                        </div>
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
@endsection

@push('scripts')
<script>
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
</script>
@endpush
