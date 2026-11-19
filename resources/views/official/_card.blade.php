{{-- Official card. Vars: $official, $lead (bool) --}}
<div class="card official-card border-0 shadow-sm rounded-4 h-100 text-center overflow-hidden {{ $lead ? 'official-card-lead' : '' }}">
    <div class="official-photo bg-light">
        @if ($official->photo)
            <img src="{{ storage_url($official->photo) }}" alt="{{ $official->name }}" loading="lazy">
        @else
            <div class="w-100 h-100 d-flex align-items-center justify-content-center">
                <i class="fas fa-user {{ $lead ? 'fa-4x' : 'fa-3x' }} text-secondary opacity-50"></i>
            </div>
        @endif
    </div>
    <div class="card-body p-3 {{ $lead ? 'p-md-4' : '' }}">
        <h3 class="{{ $lead ? 'h5' : 'h6' }} fw-bold mb-1">{{ $official->name }}</h3>
        <p class="text-primary small fw-bold mb-2">{{ $official->position }}</p>
        @if ($official->bio)
            <p class="text-muted small mb-2 official-bio">{{ $official->bio }}</p>
            @if (mb_strlen($official->bio) > 100)
                <button type="button" class="btn btn-link btn-sm p-0 fw-bold text-decoration-none"
                        data-bs-toggle="modal" data-bs-target="#official-{{ $official->id }}">
                    Selengkapnya <i class="fas fa-chevron-right ms-1 small"></i>
                </button>
                @push('official-modals')
                <div class="modal fade" id="official-{{ $official->id }}" tabindex="-1" aria-labelledby="official-{{ $official->id }}-title" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content rounded-4 border-0">
                            <div class="modal-header border-0 pb-0">
                                <div>
                                    <h2 class="h5 fw-bold mb-1" id="official-{{ $official->id }}-title">{{ $official->name }}</h2>
                                    <p class="text-primary small fw-bold mb-0">{{ $official->position }}</p>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                            </div>
                            <div class="modal-body">
                                <p class="mb-0" style="white-space: pre-line;">{{ $official->bio }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endpush
            @endif
        @endif
    </div>
</div>
