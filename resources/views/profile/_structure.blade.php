{{-- Struktur Organisasi: zoomable chart image, or a simple HTML chart when no image is uploaded. --}}
@if($profile->image)
    @php($chartUrl = storage_url($profile->image))
    <div class="card-body p-3 p-md-4 pb-0 bg-white">
        <div class="structure-scroll rounded-3 border">
            <a href="{{ $chartUrl }}" target="_blank" rel="noopener" data-bs-toggle="modal" data-bs-target="#structureModal" title="Perbesar bagan">
                <img src="{{ $chartUrl }}" class="structure-img" alt="Bagan {{ $profile->title }}">
            </a>
        </div>
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-2 small text-muted">
            <span class="d-md-none"><i class="fas fa-arrows-left-right me-1"></i>Geser bagan ke samping</span>
            <span class="ms-auto">
                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#structureModal">
                    <i class="fas fa-magnifying-glass-plus me-1"></i>Perbesar
                </button>
                <a href="{{ $chartUrl }}" target="_blank" rel="noopener" class="btn btn-sm btn-link">Buka file asli</a>
            </span>
        </div>
    </div>

    <div class="modal fade" id="structureModal" tabindex="-1" aria-label="Bagan {{ $profile->title }}" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h2 class="modal-title fs-6">{{ $profile->title }}</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                {{-- Natural image size: scroll both ways to read small text. --}}
                <div class="modal-body p-2 overflow-auto text-center">
                    <img src="{{ $chartUrl }}" class="structure-full" alt="Bagan {{ $profile->title }} ukuran penuh" loading="lazy">
                </div>
            </div>
        </div>
    </div>
@elseif(! $units)
    {{-- ponytail: fixed standard PPID levels (Permendagri 3/2017); replace by uploading the official chart image. --}}
    <div class="card-body p-4 p-md-5 pb-0 bg-white">
        <div class="structure-chart" role="list" aria-label="Bagan {{ $profile->title }}">
            @foreach([
                ['Pembina', 'Bupati Empat Lawang'],
                ['Atasan PPID', 'Sekretaris Daerah'],
                ['PPID Utama', 'Kepala Dinas Komunikasi dan Informatika'],
                ['PPID Pelaksana', 'Perangkat Daerah di lingkungan Pemerintah Kabupaten Empat Lawang'],
            ] as [$role, $holder])
                <div class="structure-node" role="listitem">
                    <div class="fw-bold">{{ $role }}</div>
                    <div class="small text-muted">{{ $holder }}</div>
                </div>
            @endforeach
        </div>
    </div>
@endif

<style>
    .structure-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; background: #f8f9fa; }
    .structure-img { display: block; width: 100%; cursor: zoom-in; }
    .structure-full { max-width: none; }
    @media (max-width: 767.98px) { .structure-img { width: auto; min-width: 720px; max-width: none; } }

    .structure-chart { display: flex; flex-direction: column; align-items: center; }
    .structure-node { position: relative; width: min(100%, 26rem); text-align: center; padding: 1rem; border: 1px solid #e5e7eb; border-top: 3px solid var(--bs-primary); border-radius: .75rem; background: #fff; }
    .structure-node + .structure-node { margin-top: 2rem; }
    .structure-node + .structure-node::before { content: ''; position: absolute; top: calc(-2rem - 2px); left: 50%; height: 2rem; border-left: 2px solid var(--bs-primary); }
</style>
