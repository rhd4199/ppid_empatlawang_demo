@extends('layouts.app')

@section('title', 'Pejabat & Pejabat Struktural')

@section('content')
<!-- Page Header -->
<div class="page-header position-relative overflow-hidden">
    <div class="position-absolute top-0 start-0 w-100 h-100 overflow-hidden" style="z-index: 0;">
        <div class="shape shape-1" style="opacity: 0.1;"></div>
        <div class="shape shape-2" style="opacity: 0.1;"></div>
    </div>

    <div class="container position-relative" style="z-index: 1;">
        <div class="row justify-content-center">
            <div class="col-md-10 text-center">
                <h1 class="display-4 fw-bold mb-3">Pejabat & Pejabat Struktural</h1>
                <nav aria-label="breadcrumb" class="d-flex justify-content-center">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-white text-opacity-75 text-decoration-none">Beranda</a></li>
                        <li class="breadcrumb-item text-white text-opacity-75">Profil</li>
                        <li class="breadcrumb-item active text-white" aria-current="page">Pejabat & Pejabat Struktural</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="container pb-5 mt-n5 position-relative" style="z-index: 2;">
    <div class="row g-4 justify-content-center">
        @forelse ($officials as $official)
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="card border-0 shadow-lg rounded-4 h-100 text-center overflow-hidden">
                    @if ($official->photo)
                        <img src="{{ storage_url($official->photo) }}" class="w-100" style="height: 220px; object-fit: cover;" alt="{{ $official->name }}">
                    @else
                        <div class="w-100 bg-light d-flex align-items-center justify-content-center" style="height: 220px;">
                            <i class="fas fa-user fa-3x text-secondary"></i>
                        </div>
                    @endif
                    <div class="card-body">
                        <h5 class="fw-bold mb-1">{{ $official->name }}</h5>
                        <p class="text-primary small fw-bold mb-2">{{ $official->position }}</p>
                        @if ($official->bio)
                            <p class="text-muted small mb-0">{{ $official->bio }}</p>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center text-muted py-5">
                <i class="fas fa-user-tie fa-3x mb-3 opacity-25"></i>
                <p>Data pejabat belum tersedia.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-5 text-center">
        <a href="{{ route('home') }}" class="btn btn-outline-primary rounded-pill px-4 py-2 fw-bold hover-scale transition-all">
            <i class="fas fa-arrow-left me-2"></i> Kembali ke Beranda
        </a>
    </div>
</div>

<style>
    .mt-n5 { margin-top: -3rem; }
    .hover-scale:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
    .transition-all { transition: all 0.3s ease; }
    .shape { position: absolute; border-radius: 50%; filter: blur(80px); }
    .shape-1 { background: #ffffff; width: 400px; height: 400px; top: -100px; right: -100px; }
    .shape-2 { background: #ffffff; width: 300px; height: 300px; bottom: -50px; left: -100px; }
</style>
@endsection
