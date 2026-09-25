@extends('layouts.admin')

@section('title', 'Dashboard')

@push('styles')
<style>
    /* Bento: big "latest" tile on the left, 2x2 category tiles on the right */
    .info-bento { display: grid; gap: 1.5rem; grid-template-columns: 1fr; }
    .info-bento .bento-tile { transition: transform .15s, box-shadow .15s; min-height: 150px; }
    .info-bento .bento-tile:hover { transform: translateY(-3px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.08) !important; }
    @media (min-width: 576px) {
        .info-bento { grid-template-columns: repeat(2, 1fr); }
        .info-bento .bento-feature { grid-column: 1 / -1; }
    }
    @media (min-width: 992px) {
        .info-bento { grid-template-columns: repeat(4, 1fr); }
        .info-bento .bento-feature { grid-column: 1 / span 2; grid-row: 1 / span 2; }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-white rounded-3 p-4">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <img src="{{ asset('assets/images/Lambang_Empat_Lawang.png') }}" alt="Lambang Kabupaten Empat Lawang" style="height: 60px; width: auto;">
                    </div>
                    <div>
                        <h4 class="fw-bold mb-1">Selamat Datang, {{ Auth::user()->name }}</h4>
                        <p class="text-muted mb-0">Dashboard Sistem Informasi Pejabat Pengelola Informasi dan Dokumentasi (PPID) Kabupaten Empat Lawang.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="text-muted small text-uppercase fw-bold">Berita Dipublish</div>
                        <div class="icon-box bg-primary bg-opacity-10 text-primary rounded p-2">
                            <i class="fas fa-newspaper"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold mb-1">{{ $stats['news'] }}</h2>
                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill small">
                        <i class="fas fa-calendar-day me-1"></i> {{ $stats['news_today'] }} Hari ini
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="text-muted small text-uppercase fw-bold">Dokumen Publik</div>
                        <div class="icon-box bg-warning bg-opacity-10 text-warning rounded p-2">
                            <i class="fas fa-file-alt"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold mb-1">{{ $stats['documents'] }}</h2>
                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill small">
                        <i class="fas fa-calendar-alt me-1"></i> {{ $stats['documents_month'] }} Bulan ini
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="text-muted small text-uppercase fw-bold">Permohonan Info</div>
                        <div class="icon-box bg-info bg-opacity-10 text-info rounded p-2">
                            <i class="fas fa-inbox"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold mb-1">{{ $stats['requests'] }}</h2>
                    <span class="badge {{ $stats['requests_pending'] > 0 ? 'bg-danger text-danger' : 'bg-success text-success' }} bg-opacity-10 rounded-pill small">
                        <i class="fas fa-exclamation-circle me-1"></i> {{ $stats['requests_pending'] }} Belum diproses
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="text-muted small text-uppercase fw-bold">Pesan Masuk</div>
                        <div class="icon-box bg-danger bg-opacity-10 text-danger rounded p-2">
                            <i class="fas fa-envelope"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold mb-1">{{ $stats['messages'] }}</h2>
                    <span class="badge {{ $stats['messages_unread'] > 0 ? 'bg-danger text-danger' : 'bg-secondary text-secondary' }} bg-opacity-10 rounded-pill small">
                        {{ $stats['messages_unread'] }} Belum dibaca
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Informasi Publik (bento) -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0">Informasi Publik</h5>
        <a href="{{ route('admin.info-public.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
            Kelola <i class="fas fa-arrow-right ms-1"></i>
        </a>
    </div>
    <div class="info-bento mb-4">
        <div class="card border-0 shadow-sm bento-feature">
            <div class="card-body d-flex flex-column">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="text-muted small text-uppercase fw-bold">Terbaru Diunggah</div>
                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill">{{ $infoCounts->sum('total') }} dokumen</span>
                </div>
                <div class="list-group list-group-flush flex-grow-1">
                    @forelse($infoLatest as $doc)
                        @php $cat = $infoCategories[$doc->category]; @endphp
                        <a href="{{ route('admin.info-public.edit', $doc->id) }}" class="list-group-item list-group-item-action px-0 d-flex align-items-center gap-3 border-0 border-bottom">
                            <div class="icon-box bg-{{ $cat['color'] }} bg-opacity-10 text-{{ $cat['color'] }} rounded p-2 flex-shrink-0">
                                <i class="fas {{ $cat['icon'] }} fa-fw"></i>
                            </div>
                            <div class="flex-grow-1 text-truncate">
                                <div class="fw-semibold text-dark text-truncate">{{ $doc->title }}</div>
                                <small class="text-muted">{{ $cat['label'] }} &middot; {{ $doc->created_at->diffForHumans() }}</small>
                            </div>
                            @unless($doc->is_published)
                                <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill flex-shrink-0">Draft</span>
                            @endunless
                        </a>
                    @empty
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-folder-open fa-2x mb-2 opacity-25"></i>
                            <p class="mb-0 small">Belum ada informasi publik.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        @foreach($infoCategories as $key => $cat)
            @php $count = $infoCounts[$key] ?? null; @endphp
            <a href="{{ route('informasi-publik.index', ['category' => \Illuminate\Support\Str::after($key, 'informasi-publik-')]) }}" target="_blank" rel="noopener"
               class="card border-0 shadow-sm text-decoration-none bento-tile" title="Lihat di situs">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-auto">
                        <div class="icon-box bg-{{ $cat['color'] }} bg-opacity-10 text-{{ $cat['color'] }} rounded p-2">
                            <i class="fas {{ $cat['icon'] }} fa-fw"></i>
                        </div>
                        <i class="fas fa-external-link-alt text-muted small"></i>
                    </div>
                    <h2 class="fw-bold text-dark mb-0 mt-3">{{ $count->total ?? 0 }}</h2>
                    <div class="text-muted small text-uppercase fw-bold">{{ $cat['label'] }}</div>
                    <small class="text-{{ $cat['color'] }}">{{ (int) ($count->published ?? 0) }} dipublikasikan</small>
                </div>
            </a>
        @endforeach
    </div>

    <div class="row g-4">
        <!-- Quick Actions -->
        <div class="col-12">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="fw-bold mb-0">Aksi Cepat</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <a href="{{ route('admin.news.create') }}" class="btn btn-outline-primary w-100 h-100 p-3 d-flex align-items-center justify-content-center flex-column gap-2">
                                <i class="fas fa-plus-circle fa-2x"></i>
                                <span>Tulis Berita</span>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('admin.galleries.create') }}" class="btn btn-outline-success w-100 h-100 p-3 d-flex align-items-center justify-content-center flex-column gap-2">
                                <i class="fas fa-images fa-2x"></i>
                                <span>Upload Galeri</span>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('admin.procurements.create') }}" class="btn btn-outline-warning w-100 h-100 p-3 d-flex align-items-center justify-content-center flex-column gap-2">
                                <i class="fas fa-file-contract fa-2x"></i>
                                <span>Info Pengadaan</span>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('admin.standard-service.create') }}" class="btn btn-outline-info w-100 h-100 p-3 d-flex align-items-center justify-content-center flex-column gap-2">
                                <i class="fas fa-file-upload fa-2x"></i>
                                <span>Upload SOP</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
