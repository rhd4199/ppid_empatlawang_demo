{{-- Official card. Vars: $official, $lead (bool). Lead = portrait card; otherwise compact horizontal card. --}}
@php
    $initials = collect(preg_split('/\s+/', $official->name))
        ->reject(fn ($w) => str_contains($w, '.'))
        ->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
@endphp
<div class="card official-card border-0 shadow-sm rounded-4 h-100 overflow-hidden {{ $lead ? 'official-card-lead text-center' : 'official-card-row' }}">
    <div class="{{ $lead ? '' : 'd-flex align-items-center gap-3 p-3 h-100' }}">
        <div class="official-photo {{ $lead ? '' : 'official-photo-sm rounded-3' }} flex-shrink-0">
            @if ($official->photo)
                <img src="{{ storage_url($official->photo) }}" alt="Foto {{ $official->name }}" loading="lazy">
            @else
                <div class="official-initials" aria-hidden="true">{{ $initials ?: '?' }}</div>
            @endif
        </div>
        <div class="{{ $lead ? 'card-body p-3 p-md-4 d-flex flex-column' : 'min-w-0 flex-grow-1' }}">
            <h3 class="{{ $lead ? 'h5' : 'h6' }} fw-bold mb-1">{{ $official->name }}</h3>
            <p class="official-position small fw-semibold mb-2">{{ $official->position }}</p>
            @if ($official->bio)
                @if ($lead)
                    <p class="text-muted small mb-3 official-bio">{{ $official->bio }}</p>
                @endif
                <button type="button" class="btn btn-sm {{ $lead ? 'btn-outline-primary rounded-pill mt-auto align-self-center px-3' : 'btn-link p-0 text-decoration-none' }} fw-bold"
                        data-bs-toggle="modal" data-bs-target="#official-{{ $official->id }}">
                    Lihat profil <i class="fas fa-chevron-right ms-1 small"></i>
                </button>
                @push('official-modals')
                <div class="modal fade" id="official-{{ $official->id }}" tabindex="-1" aria-labelledby="official-{{ $official->id }}-title" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content rounded-4 border-0">
                            <div class="modal-header border-0 pb-0 align-items-start gap-3">
                                @if ($official->photo)
                                    <img src="{{ storage_url($official->photo) }}" alt="" class="official-modal-photo rounded-3 flex-shrink-0">
                                @endif
                                <div class="me-auto">
                                    <h2 class="h5 fw-bold mb-1" id="official-{{ $official->id }}-title">{{ $official->name }}</h2>
                                    <p class="official-position small fw-semibold mb-0">{{ $official->position }}</p>
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
        </div>
    </div>
</div>
