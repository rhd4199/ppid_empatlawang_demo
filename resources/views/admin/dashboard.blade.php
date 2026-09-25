@extends('layouts.admin')

@section('title', 'Dashboard')

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
