@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1>Pengadaan Barang & Jasa</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Pengadaan</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="container pb-5">
    <div class="row">
        <div class="col-md-12">
            <ul class="nav nav-tabs nav-fill mb-4" id="procurementTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold py-3" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button" role="tab">
                        <i class="fas fa-bullhorn me-2"></i> Informasi Pengadaan
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold py-3" id="regulasi-tab" data-bs-toggle="tab" data-bs-target="#regulasi" type="button" role="tab">
                        <i class="fas fa-gavel me-2"></i> Regulasi Pengadaan
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="procurementTabsContent">
                <!-- Informasi Pengadaan -->
                <div class="tab-pane fade show active" id="info" role="tabpanel">
                    <div class="row">
                        @forelse($procurements as $procurement)
                        <div class="col-md-6 mb-4">
                            <div class="card card-hover h-100 border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="badge bg-{{ $procurement->status == 'open' ? 'success' : 'secondary' }} rounded-pill mb-2">
                                            {{ ucfirst($procurement->status) }}
                                        </span>
                                        <small class="text-muted">{{ $procurement->created_at->format('d M Y') }}</small>
                                    </div>
                                    <h5 class="card-title fw-bold">{{ $procurement->title }}</h5>
                                    <p class="card-text text-muted small mb-3">{{ Str::limit($procurement->content, 100) }}</p>
                                    <a href="{{ asset('storage/' . $procurement->file_path) }}" class="btn btn-sm btn-outline-primary rounded-pill stretched-link" target="_blank">
                                        <i class="fas fa-file-pdf me-1"></i> Lihat Detail
                                    </a>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-12">
                            <div class="alert alert-info text-center py-5">
                                <i class="fas fa-box-open fa-3x mb-3 text-muted"></i>
                                <p>Belum ada informasi pengadaan barang/jasa saat ini.</p>
                            </div>
                        </div>
                        @endforelse
                    </div>
                </div>

                <!-- Regulasi Pengadaan -->
                <div class="tab-pane fade" id="regulasi" role="tabpanel">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                @forelse($regulations as $regulation)
                                <div class="list-group-item d-flex justify-content-between align-items-center p-4">
                                    <div class="d-flex align-items-start">
                                        <div class="flex-shrink-0 me-3 text-danger">
                                            <i class="fas fa-file-pdf fa-2x"></i>
                                        </div>
                                        <div>
                                            <h5 class="mb-1 fw-bold">{{ $regulation->title }}</h5>
                                            <p class="mb-0 text-muted small">{{ $regulation->content }}</p>
                                        </div>
                                    </div>
                                    <a href="{{ asset('storage/' . $regulation->file_path) }}" class="btn btn-sm btn-outline-danger rounded-pill px-3" target="_blank">
                                        <i class="fas fa-download me-1"></i> Unduh
                                    </a>
                                </div>
                                @empty
                                <div class="p-5 text-center text-muted">
                                    <i class="fas fa-book fa-3x mb-3"></i>
                                    <p>Belum ada regulasi yang diunggah.</p>
                                </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .nav-tabs .nav-link {
        color: var(--text-light);
        border: none;
        border-bottom: 3px solid transparent;
        transition: 0.3s;
    }
    .nav-tabs .nav-link:hover {
        border-color: #e9ecef;
        color: var(--primary-color);
    }
    .nav-tabs .nav-link.active {
        color: var(--primary-color);
        border-bottom: 3px solid var(--primary-color);
        background: transparent;
    }
</style>
@endsection
