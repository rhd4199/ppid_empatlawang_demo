@extends('layouts.app')

@section('title', 'Laporan Kinerja')

@section('content')
<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1>Laporan Kinerja</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Laporan</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="container pb-5">
    <ul class="nav nav-tabs nav-fill mb-4" id="reportTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active fw-bold py-3" id="pemda-tab" data-bs-toggle="tab" data-bs-target="#pemda" type="button" role="tab">
                <i class="fas fa-building me-2"></i> Laporan Pemkab Empat Lawang
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold py-3" id="ppid-tab" data-bs-toggle="tab" data-bs-target="#ppid" type="button" role="tab">
                <i class="fas fa-file-alt me-2"></i> Laporan PPID
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold py-3" id="statistik-tab" data-bs-toggle="tab" data-bs-target="#statistik" type="button" role="tab">
                <i class="fas fa-chart-pie me-2"></i> Statistik Layanan Informasi
            </button>
        </li>
    </ul>

    <div class="tab-content" id="reportTabsContent">
        <!-- Laporan Pemda -->
        <div class="tab-pane fade show active" id="pemda" role="tabpanel">
            <div class="row">
                @forelse($reports->where('category', 'laporan_pemda') as $report)
                <div class="col-md-6 mb-4">
                    <div class="card card-hover h-100 border-0 shadow-sm">
                        <div class="card-body p-4 d-flex align-items-start">
                            <div class="flex-shrink-0 me-3">
                                <div class="bg-primary text-white rounded p-3">
                                    <i class="fas fa-chart-line fa-2x"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="card-title fw-bold">{{ $report->title }}</h5>
                                <p class="card-text text-muted small mb-3">{{ Str::limit(strip_tags($report->description), 120) }}</p>
                                <a href="{{ storage_url($report->file_path) }}" class="btn btn-sm btn-outline-primary rounded-pill stretched-link" target="_blank">
                                    <i class="fas fa-download me-1"></i> Unduh Laporan
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="alert alert-info text-center py-4">
                        <i class="fas fa-info-circle fa-2x mb-3 d-block text-primary"></i>
                        Belum ada Laporan Pemkab yang tersedia saat ini.
                    </div>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Laporan PPID -->
        <div class="tab-pane fade" id="ppid" role="tabpanel">
            <div class="row">
                @forelse($reports->where('category', 'laporan_ppid') as $report)
                <div class="col-md-6 mb-4">
                    <div class="card card-hover h-100 border-0 shadow-sm">
                        <div class="card-body p-4 d-flex align-items-start">
                            <div class="flex-shrink-0 me-3">
                                <div class="bg-success text-white rounded p-3">
                                    <i class="fas fa-file-contract fa-2x"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="card-title fw-bold">{{ $report->title }}</h5>
                                <p class="card-text text-muted small mb-3">{{ Str::limit(strip_tags($report->description), 120) }}</p>
                                <a href="{{ storage_url($report->file_path) }}" class="btn btn-sm btn-outline-success rounded-pill stretched-link" target="_blank">
                                    <i class="fas fa-download me-1"></i> Unduh Laporan
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="alert alert-info text-center py-4">
                        <i class="fas fa-info-circle fa-2x mb-3 d-block text-success"></i>
                        Belum ada Laporan PPID yang tersedia saat ini.
                    </div>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Statistik Layanan Informasi (Ringkasan Laporan Layanan Informasi Publik) -->
        <div class="tab-pane fade" id="statistik" role="tabpanel">
            <div class="row g-4 mb-4">
                <div class="col-md-3 col-sm-6">
                    <div class="card border-0 shadow-sm h-100 text-center p-4">
                        <i class="fas fa-inbox fa-2x text-primary mb-2"></i>
                        <h3 class="fw-bold mb-0">{{ $stats['total'] }}</h3>
                        <p class="text-muted small mb-0">Permohonan Diterima</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card border-0 shadow-sm h-100 text-center p-4">
                        <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                        <h3 class="fw-bold mb-0">{{ $stats['approved'] }}</h3>
                        <p class="text-muted small mb-0">Dikabulkan Sebagian/Seluruhnya</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card border-0 shadow-sm h-100 text-center p-4">
                        <i class="fas fa-times-circle fa-2x text-danger mb-2"></i>
                        <h3 class="fw-bold mb-0">{{ $stats['rejected'] }}</h3>
                        <p class="text-muted small mb-0">Ditolak</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card border-0 shadow-sm h-100 text-center p-4">
                        <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                        <h3 class="fw-bold mb-0">{{ $stats['avg_days'] ?? '-' }}</h3>
                        <p class="text-muted small mb-0">Rata-rata Hari Penyelesaian</p>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3"><i class="fas fa-list me-2 text-danger"></i>Alasan Penolakan Permohonan</h5>
                    @forelse ($stats['rejection_reasons'] as $reason)
                        <div class="border-bottom py-2 text-muted small">{{ $reason }}</div>
                    @empty
                        <p class="text-muted small mb-0">Belum ada permohonan yang ditolak, atau belum ada catatan alasan penolakan.</p>
                    @endforelse
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

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Activate tab based on hash
        var hash = window.location.hash;
        if (hash) {
            var triggerEl = document.querySelector('button[data-bs-target="' + hash + '"]');
            if (triggerEl) {
                var tab = new bootstrap.Tab(triggerEl);
                tab.show();
            }
        }

        // Update hash when tab changes
        var tabElList = [].slice.call(document.querySelectorAll('button[data-bs-toggle="tab"]'))
        tabElList.forEach(function (tabEl) {
            tabEl.addEventListener('shown.bs.tab', function (event) {
                var target = event.target.getAttribute('data-bs-target');
                if(history.pushState) {
                    history.pushState(null, null, target);
                } else {
                    window.location.hash = target;
                }
            })
        })
    });
</script>
@endpush
