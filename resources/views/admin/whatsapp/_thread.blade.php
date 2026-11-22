{{-- Right pane: open conversation (or placeholder). Rendered in the page and alone for in-page switching (show with X-WA-Pane). --}}
@php
    $filters = App\Http\Controllers\Admin\WhatsAppInboxController::filters();
    $initial = fn ($c) => mb_strtoupper(mb_substr($c->displayName() ?: 'W', 0, 1));
@endphp
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
            <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle ms-auto" data-bs-toggle="collapse" data-bs-target="#waContact" aria-expanded="{{ $errors->hasAny(['contact_name', 'label', 'notes', 'ai_memory']) ? 'true' : 'false' }}" title="Info kontak" aria-label="Info kontak"><i class="fas fa-user-pen"></i></button>
            <a href="{{ wa_link($conversation->phone) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-success rounded-circle" title="Buka di WhatsApp"><i class="fab fa-whatsapp"></i></a>
        </div>
        {{-- Contact card (CRM): officer-set name, label, notes; WhatsApp profile name stays as a hint. --}}
        <form id="waContact" action="{{ route('admin.wa-inbox.contact', [$conversation] + $filters) }}" method="POST"
              class="collapse border-bottom bg-light px-3 py-2 {{ $errors->hasAny(['contact_name', 'label', 'notes', 'ai_memory']) ? 'show' : '' }}" data-wa-contact>
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
                <div class="col-12">
                    <label class="form-label small mb-0" for="waContactMemory">Ingatan Sapa Madani <span class="text-muted">(diisi AI dari percakapan; boleh dikoreksi)</span></label>
                    <textarea id="waContactMemory" name="ai_memory" rows="2" maxlength="1500" class="form-control form-control-sm" placeholder="Belum ada">{{ old('ai_memory', $conversation->ai_memory) }}</textarea>
                </div>
                @foreach (['contact_name', 'label', 'notes', 'ai_memory'] as $field)
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
        {{-- Optimistic bubble for a reply being sent (same markup as _message, pending tick). --}}
        <template data-wa-pending>
            <div class="d-flex mb-2" data-wa-msg="" data-wa-msg-day="Hari ini">
                <div class="wa-bubble wa-out">
                    <div class="small fw-bold" style="color:#008069">Petugas PPID</div>
                    <span class="wa-text"></span><span class="wa-meta"><span data-wa-time></span><i class="wa-tick wa-tick-pending {{ App\Models\WaMessage::TICKS['pending'][0] }}" title="Menunggu" aria-label="Menunggu"></i></span>
                </div>
            </div>
        </template>
        <div class="wa-composer px-3 py-2">
            <form id="redraftForm" action="{{ route('admin.wa-inbox.redraft', $conversation) }}" method="POST">@csrf</form>
            @include('admin.whatsapp._suggest', ['pending' => $conversation->reply_draft === null && $messages->last()?->direction === 'in'])
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
