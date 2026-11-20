{{-- Daftar dokumen pengadaan: $docs, $empty (teks kosong), $limit (panjang deskripsi) --}}
<div class="list-group list-group-flush">
    @forelse($docs as $doc)
    <div class="list-group-item d-flex justify-content-between align-items-center py-3">
        <div>
            <h5 class="mb-1">{{ $doc->title }}</h5>
            <p class="mb-0 text-muted small">{{ Str::limit(strip_tags($doc->description), $limit) }}</p>
        </div>
        <div class="d-flex flex-column gap-2 ms-3">
            @if($doc->file_path)
            <a href="{{ storage_url($doc->file_path) }}" class="btn btn-sm btn-primary rounded-pill px-3 text-nowrap" target="_blank" rel="noopener">
                <i class="fas fa-eye me-1"></i> Lihat
            </a>
            @endif
            @if($doc->external_url)
            <a href="{{ $doc->external_url }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 text-nowrap" target="_blank" rel="noopener">
                <i class="fas fa-arrow-up-right-from-square me-1"></i> Buka
            </a>
            @endif
        </div>
    </div>
    @empty
    <div class="alert alert-info">{{ $empty }}</div>
    @endforelse
</div>
