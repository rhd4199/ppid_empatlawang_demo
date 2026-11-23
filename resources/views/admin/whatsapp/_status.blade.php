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
            {{-- API key: only whether it is set, never the value (it lives in the server env, not the DB). --}}
            <dt class="col-sm-4">API key Larika (<code>LARIKA_KEY</code>)</dt>
            <dd class="col-sm-8" data-wa-key="{{ config('services.larika.key') ? 'set' : 'missing' }}">
                {!! config('services.larika.key') ? '<span class="text-success">terisi</span>' : '<span class="text-danger">belum diisi</span>' !!}
                @if ($larika && $larika['error']) · <span class="text-danger">{{ $larika['error'] }}</span>@endif
                · gateway <code>{{ config('services.larika.url') }}</code>
                <details class="mt-1" @if (! config('services.larika.key') || ($larika['state'] ?? '') === 'rejected') open @endif>
                    <summary class="text-primary">Cara mengisi / mengganti API key</summary>
                    <ol class="mb-0 mt-1 ps-3 text-muted">
                        <li>Buat API key nomor di panel Larika › WA Gateway › nomor Sapa Madani › API.</li>
                        <li>Isi di env server (file <code>.env</code>): <code>LARIKA_KEY=lk_…</code>. Key sengaja tidak disimpan di database atau form ini.</li>
                        <li>Di form di bawah pilih provider <strong>Larika WhatsApp API</strong>, centang Aktif, lalu Simpan.</li>
                        <li>Nyalakan ulang penerima pesan: <code>php artisan whatsapp:larika-poll</code> (Supervisor, README "WhatsApp Larika").</li>
                        <li>Opsional, webhook cadangan (APP_URL harus https): <code>php artisan whatsapp:larika-webhook</code>, salin <code>LARIKA_WEBHOOK_KEY</code> yang dicetak ke <code>.env</code>.</li>
                    </ol>
                </details>
            </dd>
            @if ($larika)
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
                {{-- Messages arrive by webhook (LARIKA_WEBHOOK_KEY set) or by the poller; either one is enough. --}}
                @php $webhook = filled(config('services.larika.webhook_key')); @endphp
                <dt class="col-sm-4">Penerima pesan</dt>
                <dd class="col-sm-8">
                    @if ($webhook)
                        <span class="text-success">webhook aktif</span>{{ $larika['poller'] ? ' · poller juga berjalan' : '' }}
                    @else
                        {!! $larika['poller'] ? '<span class="text-success">poller berjalan</span>' : '<span class="text-danger">tidak ada (webhook belum diisi, poller tidak berjalan)</span>' !!}
                        · cek terakhir {{ $ago($larika['last_poll_at']) }}
                        @unless ($larika['poller'])<div class="text-muted">Daftarkan webhook dengan <code>php artisan whatsapp:larika-webhook</code> (isi <code>LARIKA_WEBHOOK_KEY</code>), atau jalankan <code>php artisan whatsapp:larika-poll</code> di server (README "WhatsApp Larika").</div>@endunless
                    @endif
                </dd>
                <dt class="col-sm-4">Event terakhir diterima</dt>
                <dd class="col-sm-8 mb-0">{{ $ago($larika['last_event_at']) }}</dd>
            @endif
        </dl>
    </div>
</div>
