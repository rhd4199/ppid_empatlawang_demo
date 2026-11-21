@extends('layouts.admin')

@section('title', 'Kotak Masuk WhatsApp')

@push('styles')
<style>
    .wa-inbox { height: calc(100vh - 190px); min-height: 480px; }
    .wa-list, .wa-thread { overflow-y: auto; }
    .wa-avatar { width: 42px; height: 42px; flex: 0 0 42px; border-radius: 50%; background: #25d366; color: #fff;
        display: flex; align-items: center; justify-content: center; font-weight: 700; }
    .wa-item { border-left: 3px solid transparent; color: inherit; text-decoration: none; }
    .wa-item:hover { background: #f8fafc; }
    .wa-item.active { background: #ecfdf5; border-left-color: #25d366; }
    .wa-thread { background: #efeae2; }
    .wa-bubble { max-width: 75%; padding: .5rem .75rem; border-radius: .75rem; white-space: pre-wrap; overflow-wrap: anywhere;
        box-shadow: 0 1px 1px rgba(0,0,0,.08); }
    .wa-in { background: #fff; border-top-left-radius: 0; }
    .wa-out { background: #d9fdd3; border-top-right-radius: 0; margin-left: auto; }
    @media (max-width: 991.98px) { .wa-inbox { height: calc(100vh - 160px); } }
</style>
@endpush

@section('content')
<div class="container-fluid pb-4">
    <div class="mb-3">
        <h1 class="h3 mb-1 text-gray-800 fw-bold">Kotak Masuk WhatsApp</h1>
        <p class="text-muted small mb-0">Pesan warga yang masuk lewat WhatsApp Gateway.</p>
    </div>

    @foreach (['success' => 'success', 'error' => 'danger'] as $key => $class)
        @if (session($key))
            <div class="alert alert-{{ $class }} border-0 shadow-sm py-2">{{ session($key) }}</div>
        @endif
    @endforeach
    @error('body')<div class="alert alert-danger border-0 shadow-sm py-2">{{ $message }}</div>@enderror

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="row g-0 wa-inbox">
            {{-- Conversation list; on phones hidden while a conversation is open. --}}
            <div class="col-lg-4 border-end h-100 flex-column {{ $conversation ? 'd-none d-lg-flex' : 'd-flex' }}">
                <div class="p-3 border-bottom fw-bold small text-muted">{{ $conversations->total() }} percakapan</div>
                <div class="wa-list flex-grow-1">
                    @forelse ($conversations as $c)
                        <a href="{{ route('admin.wa-inbox.show', $c) }}" class="wa-item d-flex gap-2 p-3 border-bottom {{ $conversation?->id === $c->id ? 'active' : '' }}">
                            <div class="wa-avatar">{{ mb_strtoupper(mb_substr($c->name ?: 'W', 0, 1)) }}</div>
                            <div class="flex-grow-1 min-w-0" style="min-width:0">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <div class="text-truncate">
                                        <div class="small text-success">Warga · WhatsApp</div>
                                        <div class="fw-bold text-truncate">{{ $c->name ?: '+' . $c->phone }}</div>
                                        @if ($c->name)<div class="small text-muted">+{{ $c->phone }}</div>@endif
                                    </div>
                                    <div class="text-end flex-shrink-0">
                                        <div class="small text-muted">{{ $c->last_message_at?->timezone('Asia/Jakarta')->format('d/m H:i') }}</div>
                                        @if ($c->status === 'new')<span class="badge bg-success">Baru</span>@endif
                                    </div>
                                </div>
                                <div class="small text-muted text-truncate mt-1">{{ Str::limit($c->last_body, 80) }}</div>
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

            {{-- Conversation detail; on phones shown only when one is open. --}}
            <div class="col-lg-8 h-100 flex-column {{ $conversation ? 'd-flex' : 'd-none d-lg-flex' }}">
                @if ($conversation)
                    <div class="p-3 border-bottom d-flex align-items-center gap-2 bg-white">
                        <a href="{{ route('admin.wa-inbox.index') }}" class="btn btn-sm btn-light d-lg-none" aria-label="Kembali"><i class="fas fa-arrow-left"></i></a>
                        <div class="wa-avatar">{{ mb_strtoupper(mb_substr($conversation->name ?: 'W', 0, 1)) }}</div>
                        <div class="min-w-0">
                            <div class="fw-bold">{{ $conversation->name ?: 'Warga' }}</div>
                            <div class="small text-muted">+{{ $conversation->phone }} · {{ App\Models\WaConversation::STATUSES[$conversation->status] ?? $conversation->status }}</div>
                        </div>
                        <a href="{{ wa_link($conversation->phone) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-success ms-auto"><i class="fab fa-whatsapp"></i></a>
                    </div>
                    <div class="wa-thread flex-grow-1 p-3">
                        <div class="text-center mb-3"><span class="badge bg-white text-muted shadow-sm fw-normal">Pertanyaan warga · diteruskan dari bot WhatsApp</span></div>
                        @foreach ($messages as $m)
                            <div class="d-flex mb-2">
                                <div class="wa-bubble {{ $m->direction === 'out' ? 'wa-out' : 'wa-in' }}">
                                    @if ($m->direction === 'out')<div class="small fw-bold text-success">Petugas PPID</div>@endif
                                    <div>{{ $m->body }}</div>
                                    <div class="text-end text-muted" style="font-size:.7rem">
                                        {{ $m->sent_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}
                                        @if ($m->direction === 'out' && $m->status === 'failed')<span class="text-danger" title="{{ $m->error }}">· gagal terkirim</span>@endif
                                    </div>
                                    @if ($m->status === 'failed' && $m->error)<div class="small text-danger">{{ $m->error }}</div>@endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @if ($conversation->reply_draft !== null)
                        {{-- AI draft: read-only until "Ubah"; only "Setujui & kirim" sends it to the citizen. --}}
                        <div class="border-top bg-white p-3">
                            <form id="redraftForm" action="{{ route('admin.wa-inbox.redraft', $conversation) }}" method="POST">@csrf</form>
                            <form action="{{ route('admin.wa-inbox.send', $conversation) }}" method="POST">
                                @csrf
                                <label for="waDraft" class="form-label small fw-bold mb-1"><i class="fas fa-robot text-primary me-1"></i>Draf balasan Sapa Madani (AI) — periksa sebelum dikirim</label>
                                <textarea id="waDraft" name="body" rows="5" class="form-control small bg-light" readonly required>{{ old('body', $conversation->reply_draft) }}</textarea>
                                <div class="d-flex flex-wrap gap-2 justify-content-end mt-2">
                                    <button type="submit" form="redraftForm" class="btn btn-sm btn-light rounded-pill"><i class="fas fa-rotate me-1"></i> Buat ulang draf</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill" data-wa-edit><i class="fas fa-pen me-1"></i> Ubah</button>
                                    <button type="submit" class="btn btn-sm btn-success rounded-pill fw-bold"><i class="fab fa-whatsapp me-1"></i> Setujui &amp; kirim</button>
                                </div>
                            </form>
                        </div>
                    @endif
                @else
                    <div class="flex-grow-1 d-flex align-items-center justify-content-center text-muted wa-thread">
                        <div class="text-center"><i class="fab fa-whatsapp fa-3x mb-2 text-success"></i><div>Pilih percakapan untuk membaca pesan.</div></div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Newest message in view, like a chat app.
    document.querySelectorAll('.wa-thread').forEach(el => { el.scrollTop = el.scrollHeight; });
    document.querySelector('[data-wa-edit]')?.addEventListener('click', () => {
        const draft = document.getElementById('waDraft');
        draft.readOnly = false;
        draft.classList.remove('bg-light');
        draft.focus();
    });
</script>
@endpush
