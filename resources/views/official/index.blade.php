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
        <section class="official-section mb-5" aria-labelledby="leaders-title">
            <h2 id="leaders-title" class="official-heading h4 fw-bold text-center mb-4"><i class="fas fa-landmark"></i> Pimpinan Daerah</h2>
            <div class="row g-3 g-md-4 justify-content-center">
                @foreach ($leaders as $official)
                    <div class="col-6 col-md-4 col-xl-3">
                        @include('official._card', ['lead' => true])
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    @if ($officials->isNotEmpty())
        <section class="official-section" aria-labelledby="structural-title">
            <h2 id="structural-title" class="official-heading h4 fw-bold text-center mb-4 {{ $leaders->isEmpty() ? 'visually-hidden' : '' }}"><i class="fas fa-sitemap"></i> Pejabat Struktural</h2>
            <div class="row g-3">
                @foreach ($officials as $official)
                    <div class="col-12 col-md-6 col-lg-4">
                        @include('official._card', ['lead' => false])
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <div class="mt-5 text-center">
        <a href="{{ route('profiles.index') }}" class="btn btn-outline-primary rounded-pill px-4 py-2 fw-bold">
            <i class="fas fa-arrow-left me-2"></i>Kembali ke Profil
        </a>
    </div>
</div>

@stack('official-modals')

<style>

    .shape { position: absolute; border-radius: 50%; filter: blur(80px); background: #fff; opacity: .1; }
    .shape-1 { width: 400px; height: 400px; top: -100px; right: -100px; }
    .shape-2 { width: 300px; height: 300px; bottom: -50px; left: -100px; }

    .official-heading i { color: var(--bs-primary); margin-right: .5rem; }
    .official-heading::after { content: ""; display: block; width: 56px; height: 4px; border-radius: 4px; margin: .75rem auto 0; background: linear-gradient(90deg, var(--bs-primary), #f59e0b); }

    .official-card { transition: transform .2s ease, box-shadow .2s ease; }
    .official-card:hover { transform: translateY(-3px); box-shadow: 0 .75rem 1.5rem rgba(0,0,0,.1) !important; }
    .official-card-lead { border-top: 4px solid var(--bs-primary) !important; }
    .official-photo { aspect-ratio: 3 / 4; overflow: hidden; background: linear-gradient(160deg, #e0f2fe, #bae6fd); }
    .official-photo img { width: 100%; height: 100%; object-fit: cover; object-position: top center; display: block; }
    .official-photo-sm { width: 72px; }
    .official-initials { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;
        font-weight: 800; font-size: 2.5rem; color: var(--bs-primary); letter-spacing: .05em; }
    .official-photo-sm .official-initials { font-size: 1.25rem; }
    .official-position { color: var(--bs-primary); line-height: 1.35; }
    .official-card-row .official-position { display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
    .official-card h3 { overflow-wrap: anywhere; }
    .official-bio { display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
    .official-modal-photo { width: 64px; aspect-ratio: 3 / 4; object-fit: cover; object-position: top; }

    @media (max-width: 575.98px) {
        .official-card-lead .card-body { padding: .75rem !important; }
        .official-card-lead h3 { font-size: .95rem; }
        .official-card-lead .official-position { font-size: .75rem; }
        .official-bio { display: none; }
    }
</style>
@endsection
