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
                @php
                    // Fact chips pulled from the bio text, e.g. "periode 2025–2030".
                    preg_match('/periode\s+(\d{4}\s*[–-]\s*\d{4})/u', $official->bio, $period);
                    $paragraphs = preg_split('/\n\s*\n/', trim($official->bio));
                @endphp
                <div class="modal fade official-modal" id="official-{{ $official->id }}" tabindex="-1" aria-labelledby="official-{{ $official->id }}-title" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                        <div class="modal-content border-0 rounded-4 overflow-hidden">
                            <div class="om-hero text-white text-center position-relative">
                                <span class="om-blob om-blob-1" aria-hidden="true"></span>
                                <span class="om-blob om-blob-2" aria-hidden="true"></span>
                                <i class="fas fa-landmark om-watermark" aria-hidden="true"></i>
                                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                <div class="om-photo om-anim mx-auto" style="--d: .05s">
                                    @if ($official->photo)
                                        <img src="{{ storage_url($official->photo) }}" alt="Foto {{ $official->name }}">
                                    @else
                                        <span class="official-initials" aria-hidden="true">{{ $initials ?: '?' }}</span>
                                    @endif
                                    <span class="om-badge"><i class="fas fa-circle-check"></i></span>
                                </div>
                                <h2 class="om-anim h4 fw-bold mt-3 mb-1" style="--d: .15s" id="official-{{ $official->id }}-title">{{ $official->name }}</h2>
                                <p class="om-anim om-position mb-0" style="--d: .25s"><i class="fas fa-briefcase me-2"></i>{{ $official->position }}</p>
                            </div>
                            <div class="modal-body p-4 p-md-5">
                                <div style="--d: .35s" class="om-anim d-flex flex-wrap justify-content-center gap-2 mb-4">
                                    <span class="om-chip"><i class="fas {{ $lead ? 'fa-crown' : 'fa-sitemap' }}"></i>{{ $lead ? 'Pimpinan Daerah' : 'Pejabat Struktural' }}</span>
                                    @if ($period)
                                        <span class="om-chip"><i class="fas fa-calendar-days"></i>Periode {{ $period[1] }}</span>
                                    @endif
                                    <span class="om-chip"><i class="fas fa-location-dot"></i>Kab. Empat Lawang</span>
                                </div>
                                <div class="om-anim om-bio" style="--d: .45s">
                                    <div class="om-bio-label"><i class="fas fa-id-card me-2"></i>Profil Singkat</div>
                                    @foreach ($paragraphs as $paragraph)
                                        <p class="{{ $loop->last ? 'mb-0' : '' }}">{{ $paragraph }}</p>
                                    @endforeach
                                </div>
                            </div>
                            <div class="modal-footer border-0 pt-0 px-4 pb-4 justify-content-center">
                                <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold" data-bs-dismiss="modal"><i class="fas fa-xmark me-2"></i>Tutup</button>
                            </div>
                        </div>
                    </div>
                </div>
                @endpush
            @endif
        </div>
    </div>
</div>
