{{-- Contact list items; also returned by the poll endpoint (inbox.summary). --}}
@php
    // Contact list time: 14:05 today, "Kemarin", else 19/11/26.
    $listTime = function ($date) {
        if (! $date) {
            return '';
        }
        $label = App\Models\WaMessage::dayLabel($date);

        return $label === 'Hari ini' ? $date->copy()->timezone('Asia/Jakarta')->format('H:i') : ($label === 'Kemarin' ? 'Kemarin' : $date->copy()->timezone('Asia/Jakarta')->format('d/m/y'));
    };
@endphp
@forelse ($conversations as $c)
    @php $unread = $c->status === 'new' ? max(1, (int) $c->unread_count) : 0; @endphp
    <a href="{{ route('admin.wa-inbox.show', $c) }}" class="wa-item d-flex gap-3 px-3 py-2 align-items-center {{ $currentId === $c->id ? 'active' : '' }} {{ $unread ? 'unread' : '' }}">
        <div class="wa-avatar">{{ mb_strtoupper(mb_substr($c->name ?: 'W', 0, 1)) }}</div>
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
