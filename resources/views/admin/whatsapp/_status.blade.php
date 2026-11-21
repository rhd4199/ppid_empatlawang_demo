{{-- Connection status (Larika only: other providers have no status API here). $larika from LarikaStatus::check(). --}}
@php
    $ago = fn ($date) => $date ? $date->copy()->timezone('Asia/Jakarta')->format('d/m/Y H:i') . ' WIB (' . $date->locale('id')->diffForHumans() . ')' : 'belum pernah';
    [$badge, $headline] = match (true) {
        ! $wa['enabled'] => ['secondary', 'Nonaktif'],
        $larika === null => ['secondary', 'Status tidak dicek untuk provider ini'],
        $larika['state'] === 'missing' => ['danger', 'LARIKA_KEY belum diisi'],
        $larika['state'] === 'rejected' => ['danger', 'Key ditolak'],
        $larika['state'] === 'error' => ['warning', 'Gateway tidak bisa dicek'],
        ($larika['number']['status'] ?? '') !== 'ONLINE' => ['warning', 'Nomor belum online'],
        default => ['success', 'Tersambung'],
    };
@endphp
<div class="card border-0 shadow-sm rounded-4 mb-4" data-wa-status="{{ $larika['state'] ?? 'none' }}">
    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
        <h5 class="mb-0 fw-bold"><i class="fas fa-plug me-2 text-success"></i>Status Koneksi</h5>
        <span class="badge rounded-pill bg-{{ $badge }} px-3 py-2">{{ $headline }}</span>
    </div>
    <div class="card-body p-4">
        <dl class="row small mb-0">
            <dt class="col-sm-4">Provider aktif</dt>
            <dd class="col-sm-8">{{ App\Support\WhatsAppGateway::PROVIDERS[$wa['provider']] ?? $wa['provider'] }} · {{ $wa['enabled'] ? 'aktif' : 'nonaktif' }} · bot {{ $wa['auto_reply'] ? 'membalas otomatis' : 'mati' }}</dd>
            @if ($larika)
                <dt class="col-sm-4">LARIKA_KEY (env server)</dt>
                <dd class="col-sm-8">{!! $larika['state'] === 'missing' ? '<span class="text-danger">belum diisi</span>' : '<span class="text-success">terisi</span>' !!}@if ($larika['error']) · <span class="text-danger">{{ $larika['error'] }}</span>@endif</dd>
                @if ($n = $larika['number'])
                    <dt class="col-sm-4">Nomor</dt>
                    <dd class="col-sm-8">
                        +{{ $n['phone'] }}{{ $n['profile'] ? ' · ' . $n['profile'] : '' }} ·
                        <span class="fw-semibold text-{{ $n['status'] === 'ONLINE' ? 'success' : 'warning' }}">{{ $n['status'] }}</span>
                        @if ($n['status'] !== 'ONLINE')<span class="text-muted">— sambungkan ulang / scan QR di panel Larika › WA Gateway.</span>@endif
                    </dd>
                    <dt class="col-sm-4">Kiriman hari ini</dt>
                    <dd class="col-sm-8">{{ $n['cap_used'] ?? 0 }} / {{ $n['daily_cap'] ?? '—' }}{{ $n['risk'] ? ' · risiko blokir ' . $n['risk'] : '' }}</dd>
                @endif
                <dt class="col-sm-4">Penerima pesan (poller)</dt>
                <dd class="col-sm-8">
                    {!! $larika['poller'] ? '<span class="text-success">berjalan</span>' : '<span class="text-danger">tidak berjalan</span>' !!}
                    · cek terakhir {{ $ago($larika['last_poll_at']) }}
                    @unless ($larika['poller'])<div class="text-muted">Jalankan <code>php artisan whatsapp:larika-poll</code> di server (README "WhatsApp Larika").</div>@endunless
                </dd>
                <dt class="col-sm-4">Event terakhir diterima</dt>
                <dd class="col-sm-8 mb-0">{{ $ago($larika['last_event_at']) }}</dd>
            @endif
        </dl>
    </div>
</div>
