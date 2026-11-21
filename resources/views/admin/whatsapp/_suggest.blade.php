{{-- AI suggestion card: sent only by an officer ("Setujui & kirim"), or copied into the input to edit.
     $pending: no draft yet for the citizen's last message — the inbox script loads it (redraft, JSON) so opening a chat never waits for the AI. --}}
@if ($pending || filled($conversation->reply_draft))
    <form action="{{ route('admin.wa-inbox.send', $conversation) }}" method="POST" class="wa-suggest bg-white rounded-3 shadow-sm p-2 mb-2" data-wa-suggest
          @if ($pending) data-wa-draft-load="{{ route('admin.wa-inbox.redraft', $conversation) }}" @endif>
        @csrf
        <input type="hidden" name="body" value="{{ $conversation->reply_draft }}">
        <div class="small fw-bold mb-1"><i class="fas fa-robot text-primary me-1"></i>Saran balasan Sapa Madani (AI) — periksa sebelum dikirim</div>
        @if ($pending)
            <div class="wa-suggest-text small text-muted" data-wa-suggest-text><i class="fas fa-spinner fa-spin me-1"></i>Menyiapkan draf…</div>
        @else
            <div class="wa-suggest-text small" data-wa-suggest-text>{{ $conversation->reply_draft }}</div>
            <div class="d-flex flex-wrap gap-2 justify-content-end mt-2">
                <button type="submit" form="redraftForm" class="btn btn-sm btn-light rounded-pill"><i class="fas fa-rotate me-1"></i> Buat ulang</button>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" data-wa-use><i class="fas fa-pen me-1"></i> Pakai/Ubah</button>
                <button type="submit" class="btn btn-sm btn-success rounded-pill fw-bold"><i class="fas fa-paper-plane me-1"></i> Setujui &amp; kirim</button>
            </div>
        @endif
    </form>
@endif
