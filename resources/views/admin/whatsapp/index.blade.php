@extends('layouts.admin')

@section('title', 'Pengaturan WhatsApp')

@section('content')
<div class="container-fluid pb-5">
    <div class="mb-3">
        <h1 class="h3 mb-1 text-gray-800 fw-bold">WhatsApp</h1>
        <p class="text-muted small mb-0">Koneksi ke provider WhatsApp untuk mengirim notifikasi otomatis.</p>
    </div>
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item"><a class="nav-link" href="{{ route('admin.wa-inbox.index') }}"><i class="fas fa-comments me-1"></i> Chat</a></li>
        <li class="nav-item"><a class="nav-link active" aria-current="page" href="{{ route('admin.whatsapp.index') }}"><i class="fas fa-gear me-1"></i> Pengaturan</a></li>
    </ul>

    @foreach (['success' => 'success', 'error' => 'danger'] as $key => $class)
        @if (session($key))
            <div class="alert alert-{{ $class }} border-0 shadow-sm">{{ session($key) }}</div>
        @endif
    @endforeach
    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm">
            <ul class="mb-0 small">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold"><i class="fab fa-whatsapp me-2 text-success"></i>Pengaturan Koneksi</h5>
        </div>
        <form action="{{ route('admin.whatsapp.update') }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body p-4">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" role="switch" id="waEnabled" name="enabled" value="1" {{ old('enabled', $wa['enabled']) ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold" for="waEnabled">Aktif (kirim notifikasi WhatsApp)</label>
                </div>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" role="switch" id="waAutoReply" name="auto_reply" value="1" {{ old('auto_reply', $wa['auto_reply']) ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold" for="waAutoReply">Bot Sapa Madani membalas otomatis</label>
                    <div class="small text-muted">Pertanyaan umum (jam layanan, alamat, biaya, lama proses, persyaratan layanan, salam) dijawab bot langsung. Permintaan dokumen/permohonan, aduan, keberatan, dan pertanyaan yang tidak dipahami bot diteruskan ke Kotak Masuk WA. Mati = semua pesan menunggu petugas.</div>
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Provider</label>
                        <select name="provider" class="form-select">
                            @foreach (App\Support\WhatsAppGateway::PROVIDERS as $value => $label)
                                <option value="{{ $value }}" {{ old('provider', $wa['provider']) === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-bold">URL API</label>
                        <input type="url" name="api_url" class="form-control" value="{{ old('api_url', $wa['api_url']) }}" placeholder="https://...">
                        <small class="text-muted">Kosong = bawaan (Fonnte: api.fonnte.com, Cloud API: graph.facebook.com/v21.0). Wablas: server akun (mis. https://jkt.wablas.com). WAHA: alamat server sendiri.</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Token / API Key</label>
                        {{-- The stored token is never printed back. --}}
                        <input type="password" name="token" class="form-control" autocomplete="new-password"
                               placeholder="{{ $wa['has_token'] ? '•••••••• (tersimpan — kosongkan bila tidak diganti)' : 'Belum diisi' }}">
                        {{-- Larika key lives in the server env only; show whether it is set, never the value. --}}
                        <small class="text-muted d-block">Larika WhatsApp API: kolom ini tidak dipakai — key dibaca dari env server <code>LARIKA_KEY</code>
                            ({!! config('services.larika.key') ? '<span class="text-success">terisi</span>' : '<span class="text-danger">belum diisi</span>' !!}), URL <code>{{ config('services.larika.url') }}</code>.</small>
                        @if ($wa['has_token'])
                            <div class="form-check mt-1">
                                <input class="form-check-input" type="checkbox" name="clear_token" value="1" id="clearToken">
                                <label class="form-check-label small" for="clearToken">Hapus token tersimpan</label>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Nomor WA Pengirim</label>
                        <input type="text" name="sender" class="form-control" value="{{ old('sender', $wa['sender']) }}" placeholder="mis. 628972007121">
                        <small class="text-muted">Cloud API: isi <strong>Phone Number ID</strong> dari Meta. Fonnte/Wablas/WAHA: pengirim mengikuti perangkat token (hanya catatan).</small>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold">URL Webhook (pesan masuk)</label>
                        <input type="text" class="form-control font-monospace small" value="{{ $webhookUrl }}" readonly onclick="this.select()">
                        <small class="text-muted">Tempel di pengaturan webhook provider. Rahasiakan — siapa pun yang tahu URL ini bisa memasukkan pesan. Cloud API: Verify Token = nilai <code>secret</code> di URL.</small>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white text-end p-3">
                <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold"><i class="fas fa-save me-2"></i> Simpan</button>
            </div>
        </form>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold"><i class="fas fa-paper-plane me-2 text-primary"></i>Kirim Pesan Uji</h5>
            <small class="text-muted">Memakai pengaturan tersimpan (simpan dulu), walau status belum aktif.</small>
        </div>
        <form action="{{ route('admin.whatsapp.test') }}" method="POST">
            @csrf
            <div class="card-body p-4 row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Nomor Tujuan</label>
                    <input type="text" name="test_phone" class="form-control" value="{{ old('test_phone') }}" placeholder="0812-3456-7890" required>
                </div>
                <div class="col-md-8">
                    <label class="form-label fw-bold">Pesan</label>
                    <input type="text" name="test_message" class="form-control" value="{{ old('test_message') }}" placeholder="Pesan uji WhatsApp Gateway PPID Kabupaten Empat Lawang.">
                </div>
            </div>
            <div class="card-footer bg-white text-end p-3">
                <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold"><i class="fab fa-whatsapp me-2"></i> Kirim pesan uji</button>
            </div>
        </form>
    </div>
</div>
@endsection
