@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1>Standar Layanan Publik</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Standar Layanan</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="container pb-5">
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card shadow-sm border-0 sticky-top" style="top: 100px; z-index: 1;">
                <div class="list-group list-group-flush rounded-3">
                    <a href="#alur" class="list-group-item list-group-item-action active py-3" data-bs-toggle="list">
                        <i class="fas fa-project-diagram me-2 w-20"></i> Alur Layanan
                    </a>
                    <a href="#tata-cara" class="list-group-item list-group-item-action py-3" data-bs-toggle="list">
                        <i class="fas fa-clipboard-list me-2 w-20"></i> Tata Cara
                    </a>
                    <a href="#sop" class="list-group-item list-group-item-action py-3" data-bs-toggle="list">
                        <i class="fas fa-book me-2 w-20"></i> SOP PPID
                    </a>
                    <a href="#maklumat" class="list-group-item list-group-item-action py-3" data-bs-toggle="list">
                        <i class="fas fa-scroll me-2 w-20"></i> Maklumat Pelayanan
                    </a>
                    <a href="#biaya" class="list-group-item list-group-item-action py-3" data-bs-toggle="list">
                        <i class="fas fa-coins me-2 w-20"></i> Biaya Pelayanan
                    </a>
                    <a href="#sengketa" class="list-group-item list-group-item-action py-3" data-bs-toggle="list">
                        <i class="fas fa-gavel me-2 w-20"></i> Penyelesaian Sengketa
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <div class="tab-content">
                <!-- Alur Layanan -->
                <div class="tab-pane fade show active" id="alur">
                    <div class="card card-hover border-0 shadow-sm p-4 mb-4">
                        <h3 class="mb-4 border-bottom pb-2">Alur Layanan Informasi</h3>
                        <div class="row">
                            @forelse($documents->where('category', 'standar_layanan_alur') as $doc)
                            <div class="col-md-6 mb-3">
                                <div class="card h-100 border bg-light">
                                    <div class="card-body">
                                        <h5 class="card-title text-primary">{{ $doc->title }}</h5>
                                        <p class="card-text text-muted small">{{ $doc->description }}</p>
                                        <a href="{{ asset('storage/' . $doc->file_path) }}" class="btn btn-sm btn-outline-primary stretched-link" target="_blank">Lihat Dokumen</a>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="col-12"><div class="alert alert-info">Belum ada dokumen alur layanan.</div></div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Tata Cara -->
                <div class="tab-pane fade" id="tata-cara">
                    <div class="card card-hover border-0 shadow-sm p-4 mb-4">
                        <h3 class="mb-4 border-bottom pb-2">Tata Cara Permohonan</h3>
                        <div class="list-group list-group-flush">
                            @forelse($documents->where('category', 'standar_layanan_tata_cara') as $doc)
                            <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                                <div>
                                    <h5 class="mb-1">{{ $doc->title }}</h5>
                                    <p class="mb-0 text-muted small">{{ $doc->description }}</p>
                                </div>
                                <a href="{{ asset('storage/' . $doc->file_path) }}" class="btn btn-sm btn-primary rounded-pill px-3" target="_blank"><i class="fas fa-download me-1"></i> Unduh</a>
                            </div>
                            @empty
                            <div class="alert alert-info">Belum ada dokumen tata cara.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- SOP -->
                <div class="tab-pane fade" id="sop">
                    <div class="card card-hover border-0 shadow-sm p-4 mb-4">
                        <h3 class="mb-4 border-bottom pb-2">Standar Operasional Prosedur (SOP)</h3>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nama Dokumen</th>
                                        <th>Deskripsi</th>
                                        <th class="text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($documents->where('category', 'standar_layanan_sop') as $doc)
                                    <tr>
                                        <td class="fw-bold">{{ $doc->title }}</td>
                                        <td>{{ $doc->description }}</td>
                                        <td class="text-end">
                                            <a href="{{ asset('storage/' . $doc->file_path) }}" class="btn btn-sm btn-outline-success rounded-pill" target="_blank">Download</a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="3" class="text-center text-muted py-3">Belum ada data SOP.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Maklumat -->
                <div class="tab-pane fade" id="maklumat">
                    <div class="card card-hover border-0 shadow-sm p-4 mb-4">
                        <h3 class="mb-4 border-bottom pb-2">Maklumat Pelayanan</h3>
                        @forelse($documents->where('category', 'standar_layanan_maklumat') as $doc)
                        <div class="text-center mb-4">
                            <img src="{{ asset('storage/' . $doc->file_path) }}" alt="{{ $doc->title }}" class="img-fluid shadow-sm rounded mb-3" style="max-height: 400px; object-fit: contain;">
                            <h5>{{ $doc->title }}</h5>
                            <p class="text-muted">{{ $doc->description }}</p>
                            <a href="{{ asset('storage/' . $doc->file_path) }}" class="btn btn-primary rounded-pill mt-2" target="_blank">Unduh Maklumat</a>
                        </div>
                        @empty
                        <div class="alert alert-info">Belum ada maklumat pelayanan.</div>
                        @endforelse
                    </div>
                </div>

                <!-- Biaya -->
                <div class="tab-pane fade" id="biaya">
                    <div class="card card-hover border-0 shadow-sm p-4 mb-4">
                        <h3 class="mb-4 border-bottom pb-2">Biaya Pelayanan</h3>
                         @forelse($documents->where('category', 'standar_layanan_biaya') as $doc)
                        <div class="alert alert-warning border-start border-5 border-warning">
                            <h4 class="alert-heading"><i class="fas fa-info-circle me-2"></i> {{ $doc->title }}</h4>
                            <p>{{ $doc->description }}</p>
                            <hr>
                            <a href="{{ asset('storage/' . $doc->file_path) }}" class="btn btn-sm btn-outline-dark" target="_blank">Lihat Rincian Biaya</a>
                        </div>
                        @empty
                         <div class="alert alert-success border-start border-5 border-success">
                            <h4 class="alert-heading"><i class="fas fa-check-circle me-2"></i> Gratis!</h4>
                            <p>Layanan informasi publik di Kabupaten Empat Lawang tidak dipungut biaya (GRATIS), kecuali untuk biaya penggandaan atau perekaman dokumen yang timbul sesuai dengan peraturan perundang-undangan yang berlaku.</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                <!-- Sengketa -->
                <div class="tab-pane fade" id="sengketa">
                    <div class="card card-hover border-0 shadow-sm p-4 mb-4">
                        <h3 class="mb-4 border-bottom pb-2">Penyelesaian Sengketa</h3>
                        @forelse($documents->where('category', 'standar_layanan_sengketa') as $doc)
                        <div class="d-flex align-items-start mb-3">
                            <div class="flex-shrink-0">
                                <span class="badge bg-primary rounded-circle p-3"><i class="fas fa-balance-scale fa-lg"></i></span>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5>{{ $doc->title }}</h5>
                                <p>{{ $doc->description }}</p>
                                <a href="{{ asset('storage/' . $doc->file_path) }}" class="btn btn-sm btn-outline-primary rounded-pill" target="_blank">Pelajari Prosedur</a>
                            </div>
                        </div>
                        @empty
                        <div class="alert alert-info">Belum ada informasi penyelesaian sengketa.</div>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
    .w-20 { width: 25px; text-align: center; }
    .list-group-item.active {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }
</style>
@endsection
