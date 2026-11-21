{{-- One chat bubble; also returned by the poll endpoint (inbox.messages). --}}
@php $tick = $m->direction === 'out' ? (App\Models\WaMessage::TICKS[$m->status] ?? null) : null; @endphp
<div class="d-flex mb-2" data-wa-msg="{{ $m->id }}" data-wa-msg-day="{{ App\Models\WaMessage::dayLabel($m->sent_at) }}">
    <div class="wa-bubble {{ $m->direction === 'out' ? 'wa-out' : 'wa-in' }}">
        @if ($m->direction === 'out')<div class="small fw-bold" style="color:#008069">{{ $m->sender === 'bot' ? 'Bot Sapa Madani (otomatis)' : 'Petugas PPID' }}</div>@endif
        <span class="wa-text">{{ $m->body }}</span><span class="wa-meta">{{ $m->sent_at->timezone('Asia/Jakarta')->format('H:i') }}@if ($tick)<i class="wa-tick wa-tick-{{ $m->status }} {{ $tick[0] }}" title="{{ $tick[1] }}" aria-label="{{ $tick[1] }}"></i>@endif</span>
        @if ($m->status === 'failed')<div class="small text-danger mt-1" style="clear:both">Gagal terkirim{{ $m->error ? ': ' . $m->error : '' }}</div>@endif
    </div>
</div>
