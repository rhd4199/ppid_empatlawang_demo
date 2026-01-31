@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-white rounded-3 p-4">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 bg-light rounded-circle p-3 me-3">
                        <i class="fas fa-smile-beam fa-2x text-primary"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-1">Selamat Datang Kembali, {{ Auth::user()->name }}!</h4>
                        <p class="text-muted mb-0">Ini adalah ringkasan aktivitas di portal PPID Empat Lawang hari ini.</p>
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
                    <h2 class="fw-bold mb-1">12</h2>
                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill small">
                        <i class="fas fa-arrow-up me-1"></i> +2 Hari ini
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
                    <h2 class="fw-bold mb-1">45</h2>
                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill small">
                        <i class="fas fa-arrow-up me-1"></i> +5 Bulan ini
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="text-muted small text-uppercase fw-bold">Permohonan Masuk</div>
                        <div class="icon-box bg-info bg-opacity-10 text-info rounded p-2">
                            <i class="fas fa-inbox"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold mb-1">8</h2>
                    <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill small">
                        <i class="fas fa-exclamation-circle me-1"></i> 3 Belum dibaca
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="text-muted small text-uppercase fw-bold">Pengaduan</div>
                        <div class="icon-box bg-danger bg-opacity-10 text-danger rounded p-2">
                            <i class="fas fa-bullhorn"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold mb-1">2</h2>
                    <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill small">
                        Menunggu respon
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Recent Activity -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="fw-bold mb-0">Aktivitas Terbaru</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item px-4 py-3 d-flex align-items-start border-0 border-bottom">
                            <div class="flex-shrink-0 bg-light rounded p-2 me-3 text-center" style="width: 40px;">
                                <i class="fas fa-upload text-primary"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1 fw-bold">Upload Dokumen Laporan Keuangan 2023</h6>
                                <p class="mb-0 text-muted small">Oleh Admin Utama &bull; 2 jam yang lalu</p>
                            </div>
                        </div>
                        <div class="list-group-item px-4 py-3 d-flex align-items-start border-0 border-bottom">
                            <div class="flex-shrink-0 bg-light rounded p-2 me-3 text-center" style="width: 40px;">
                                <i class="fas fa-envelope text-info"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1 fw-bold">Permohonan Informasi Baru dari Budi Santoso</h6>
                                <p class="mb-0 text-muted small">ID Tiket: #REQ-2023-001 &bull; 5 jam yang lalu</p>
                            </div>
                        </div>
                        <div class="list-group-item px-4 py-3 d-flex align-items-start border-0">
                            <div class="flex-shrink-0 bg-light rounded p-2 me-3 text-center" style="width: 40px;">
                                <i class="fas fa-edit text-warning"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1 fw-bold">Update Profil Pejabat Struktural</h6>
                                <p class="mb-0 text-muted small">Oleh Admin Utama &bull; Kemarin</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white border-0 text-center py-3">
                    <a href="#" class="text-decoration-none fw-bold small">Lihat Semua Aktivitas <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="fw-bold mb-0">Aksi Cepat</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-3">
                        <a href="{{ route('news.create') }}" class="btn btn-outline-primary text-start p-3">
                            <i class="fas fa-plus-circle me-2"></i> Tulis Berita Baru
                        </a>
                        <a href="{{ route('galleries.create') }}" class="btn btn-outline-success text-start p-3">
                            <i class="fas fa-image me-2"></i> Upload Galeri Foto
                        </a>
                        <a href="{{ route('procurements.create') }}" class="btn btn-outline-warning text-start p-3">
                            <i class="fas fa-file-contract me-2"></i> Umumkan Pengadaan
                        </a>
                        <a href="{{ route('standard-service.create') }}" class="btn btn-outline-info text-start p-3">
                            <i class="fas fa-file-upload me-2"></i> Upload Dokumen SOP
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
