@extends('layouts.app')

@section('title', 'Pejabat & Pejabat Struktural')
@section('description', 'Daftar pejabat dan pejabat struktural Pemerintah Kabupaten Empat Lawang beserta jabatannya.')

@section('content')
<!-- Page Header -->
<div class="page-header position-relative overflow-hidden">
    <div class="position-absolute top-0 start-0 w-100 h-100 overflow-hidden" style="z-index: 0;">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
    </div>

    <div class="container position-relative" style="z-index: 1;">
        <div class="row justify-content-center">
            <div class="col-md-10 text-center">
                <h1 class="display-4 fw-bold mb-3">Pejabat & Pejabat Struktural</h1>
                <nav aria-label="breadcrumb" class="d-flex justify-content-center">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-white text-opacity-75 text-decoration-none">Beranda</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('profiles.index') }}" class="text-white text-opacity-75 text-decoration-none">Profil</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">Pejabat & Pejabat Struktural</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="container pb-5 official-page position-relative" style="z-index: 2;">
    @if ($leaders->isEmpty() && $officials->isEmpty())
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body text-center text-muted py-5">
                <i class="fas fa-user-tie fa-3x mb-3 opacity-25"></i>
                <p class="mb-0">Data pejabat belum tersedia.</p>
            </div>
        </div>
    @endif

    @if ($leaders->isNotEmpty())
        <h2 class="visually-hidden">Pimpinan Daerah</h2>
        <div class="row g-3 g-md-4 justify-content-center mb-4">
            @foreach ($leaders as $official)
                <div class="col-6 col-md-4 col-lg-3">
                    @include('official._card', ['lead' => true])
                </div>
            @endforeach
        </div>
    @endif

    @if ($officials->isNotEmpty())
        <h2 class="h4 fw-bold text-center mb-4 {{ $leaders->isEmpty() ? 'visually-hidden' : '' }}">Pejabat Struktural</h2>
        <div class="row g-3 g-md-4">
            @foreach ($officials as $official)
                <div class="col-6 col-md-4 col-lg-3 col-xl-2">
                    @include('official._card', ['lead' => false])
                </div>
            @endforeach
        </div>
    @endif

    <div class="mt-5 text-center">
        <a href="{{ route('profiles.index') }}" class="btn btn-outline-primary rounded-pill px-4 py-2 fw-bold">
            <i class="fas fa-arrow-left me-2"></i>Kembali ke Profil
        </a>
    </div>
</div>

@stack('official-modals')

<style>
    .official-page { margin-top: -3rem; }

    .shape { position: absolute; border-radius: 50%; filter: blur(80px); background: #fff; opacity: .1; }
    .shape-1 { width: 400px; height: 400px; top: -100px; right: -100px; }
    .shape-2 { width: 300px; height: 300px; bottom: -50px; left: -100px; }

    .official-card { transition: transform .2s ease, box-shadow .2s ease; }
    .official-card:hover { transform: translateY(-3px); box-shadow: 0 .75rem 1.5rem rgba(0,0,0,.1) !important; }
    .official-photo { aspect-ratio: 3 / 4; overflow: hidden; }
    .official-photo img { width: 100%; height: 100%; object-fit: cover; object-position: top center; display: block; }
    .official-card-lead { border-top: 4px solid var(--bs-primary) !important; }
    .official-bio { display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }

    @media (max-width: 575.98px) {
        .official-card .card-body { padding: .75rem !important; }
        .official-card h3 { font-size: .95rem; }
        .official-bio { -webkit-line-clamp: 2; }
    }
</style>
@endsection
