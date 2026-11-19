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
            {{-- Row 1: Bupati & Wakil (order 1–2); row 2: Sekretaris Daerah (order 3). --}}
            @foreach ($leaders->groupBy(fn ($o) => $o->order <= 2 ? 'heads' : 'secretary') as $row => $group)
                <div class="row g-3 g-md-4 justify-content-center {{ $loop->last ? '' : 'mb-3 mb-md-4' }}" data-row="{{ $row }}">
                    @foreach ($group as $official)
                        <div class="col-6 col-md-4 col-xl-3">
                            @include('official._card', ['lead' => true])
                        </div>
                    @endforeach
                </div>
            @endforeach
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

    /* Profile modal: gradient hero, animated photo ring, staggered fade-up content */
    .official-modal .modal-dialog { transform: scale(.9) translateY(30px); transition: transform .35s cubic-bezier(.2,.9,.3,1.3); }
    .official-modal.show .modal-dialog { transform: none; }
    .official-modal .modal-content { box-shadow: 0 2rem 4rem rgba(0,0,0,.3); }
    .om-hero { padding: 2.5rem 1.5rem 2rem; background: linear-gradient(135deg, var(--bs-primary), #0b3d91 55%, #f59e0b); background-size: 200% 200%; animation: om-gradient 8s ease infinite; overflow: hidden; }
    .om-blob { position: absolute; border-radius: 50%; background: rgba(255,255,255,.15); animation: om-float 6s ease-in-out infinite; }
    .om-blob-1 { width: 180px; height: 180px; top: -60px; left: -50px; }
    .om-blob-2 { width: 120px; height: 120px; bottom: -40px; right: 10%; animation-delay: -3s; }
    .om-watermark { position: absolute; right: 1.5rem; bottom: 1rem; font-size: 6rem; opacity: .08; }
    .om-photo { position: relative; width: 140px; height: 140px; border-radius: 50%; padding: 5px; }
    .om-photo::before { content: ""; position: absolute; inset: 0; border-radius: 50%; background: conic-gradient(#fff, #f59e0b, #fff, #38bdf8, #fff); animation: om-spin 6s linear infinite; }
    .om-photo img, .om-photo .official-initials { position: relative; width: 100%; height: 100%; border-radius: 50%; object-fit: cover; object-position: top;
        border: 4px solid #fff; background: #e0f2fe; }
    .om-photo .official-initials { font-size: 2.75rem; }
    .om-badge { position: absolute; right: 6px; bottom: 6px; width: 2rem; height: 2rem; border-radius: 50%; background: #22c55e; color: #fff;
        display: flex; align-items: center; justify-content: center; border: 3px solid #fff; animation: om-pulse 2s ease-out infinite; }
    .om-hero h2 { text-shadow: 0 2px 8px rgba(0,0,0,.25); overflow-wrap: anywhere; position: relative; }
    .om-position { display: inline-block; position: relative; background: rgba(255,255,255,.18); backdrop-filter: blur(4px); border: 1px solid rgba(255,255,255,.3);
        border-radius: 999px; padding: .35rem 1rem; font-weight: 600; font-size: .9rem; }
    .om-chip { display: inline-flex; align-items: center; gap: .4rem; padding: .4rem .85rem; border-radius: 999px; font-size: .85rem; font-weight: 600;
        background: rgba(13,110,253,.08); color: var(--bs-primary); border: 1px solid rgba(13,110,253,.15); }
    .om-chip:nth-child(2) { background: rgba(245,158,11,.1); color: #b45309; border-color: rgba(245,158,11,.25); }
    .om-chip:nth-child(3) { background: rgba(34,197,94,.1); color: #15803d; border-color: rgba(34,197,94,.25); }
    .om-bio { position: relative; background: #f8fafc; border-left: 4px solid var(--bs-primary); border-radius: 1rem; padding: 1.25rem 1.5rem; line-height: 1.8; color: #444; }
    .om-bio-label { font-size: .8rem; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: var(--bs-primary); margin-bottom: .5rem; }
    .official-modal .om-anim { opacity: 0; }
    .official-modal.show .om-anim { animation: om-up .5s ease var(--d, .1s) forwards; }
    @keyframes om-up { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: none; } }
    @keyframes om-spin { to { transform: rotate(360deg); } }
    @keyframes om-float { 50% { transform: translate(20px, 15px) scale(1.1); } }
    @keyframes om-gradient { 50% { background-position: 100% 100%; } }
    @keyframes om-pulse { 0% { box-shadow: 0 0 0 0 rgba(34,197,94,.6); } 100% { box-shadow: 0 0 0 12px rgba(34,197,94,0); } }
    @media (prefers-reduced-motion: reduce) {
        .official-modal *, .official-modal .modal-dialog { animation: none !important; transition: none !important; }
        .official-modal .om-anim { opacity: 1; }
    }

    @media (max-width: 575.98px) {
        .official-card-lead .card-body { padding: .75rem !important; }
        .official-card-lead h3 { font-size: .95rem; }
        .official-card-lead .official-position { font-size: .75rem; }
        .official-bio { display: none; }
    }
</style>
@endsection
