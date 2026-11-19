@extends('layouts.app')

@section('title', $profile->title)
@section('description', plain_text($profile->content) ?: $profile->title.' PPID Kabupaten Empat Lawang.')

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
                <h1 class="display-4 fw-bold mb-3">{{ $profile->title }}</h1>
                <nav aria-label="breadcrumb" class="d-flex justify-content-center">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-white text-opacity-75 text-decoration-none">Beranda</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('profiles.index') }}" class="text-white text-opacity-75 text-decoration-none">Profil</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">{{ $profile->title }}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="container pb-5 profile-page position-relative" style="z-index: 2;">
    <div class="row g-4">
        <!-- Side menu (desktop) / horizontal tabs (mobile) -->
        <div class="col-lg-3">
            <nav class="profile-menu card border-0 shadow-sm rounded-4 p-2" aria-label="Menu profil">
                <div class="small text-uppercase text-muted fw-bold px-3 pt-2 pb-1 d-none d-lg-block">Profil PPID</div>
                <ul class="nav nav-pills">
                    @foreach($menu as $item)
                        <li class="nav-item">
                            <a href="{{ $item['url'] }}" class="nav-link rounded-3 {{ $item['active'] ? 'active' : '' }}" @if($item['active']) aria-current="page" @endif>
                                {{ $item['title'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>
        </div>

        <div class="col-lg-9">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                @if($profile->image)
                    <img src="{{ storage_url($profile->image) }}" class="img-fluid w-100 object-fit-cover"
                         @if($profile->type != 'struktur') style="max-height: 500px;" @endif alt="{{ $profile->title }}">
                @endif

                <div class="card-body p-4 p-md-5 bg-white">
                    <div class="article-content">
                        {!! $profile->content !!}
                    </div>
                </div>
            </div>

            <nav class="d-flex justify-content-between gap-3 mt-4" aria-label="Navigasi halaman profil">
                @if($prev)
                    <a href="{{ $prev['url'] }}" rel="prev" class="btn btn-outline-primary rounded-pill px-4 py-2 fw-bold text-start">
                        <i class="fas fa-arrow-left me-2"></i>{{ $prev['title'] }}
                    </a>
                @else
                    <span></span>
                @endif
                @if($next)
                    <a href="{{ $next['url'] }}" rel="next" class="btn btn-outline-primary rounded-pill px-4 py-2 fw-bold text-end">
                        {{ $next['title'] }}<i class="fas fa-arrow-right ms-2"></i>
                    </a>
                @endif
            </nav>
        </div>
    </div>
</div>

<style>
    .profile-page { margin-top: -3rem; }

    .shape { position: absolute; border-radius: 50%; filter: blur(80px); background: #fff; opacity: .1; }
    .shape-1 { width: 400px; height: 400px; top: -100px; right: -100px; }
    .shape-2 { width: 300px; height: 300px; bottom: -50px; left: -100px; }

    /* Mobile: one scrollable row of tabs above the content */
    .profile-menu .nav { flex-wrap: nowrap; overflow-x: auto; gap: .25rem; scrollbar-width: thin; }
    .profile-menu .nav-link { white-space: nowrap; color: #444; }
    .profile-menu .nav-link:not(.active):hover { background: rgba(0,0,0,.05); }
    @media (min-width: 992px) {
        .profile-menu { position: sticky; top: 6rem; }
        .profile-menu .nav { flex-direction: column; overflow: visible; }
        .profile-menu .nav-link { white-space: normal; }
    }

    .profile-page .article-content { font-size: 1.1rem; line-height: 1.8; color: #444; }
    .profile-page .article-content :is(h1, h2, h3, h4, h5, h6) { color: #222; font-weight: 700; line-height: 1.3; margin: 1.75rem 0 .75rem; }
    .profile-page .article-content > :first-child { margin-top: 0; }
    .profile-page .article-content > :last-child { margin-bottom: 0; }
    .profile-page .article-content h2 { font-size: 1.6rem; }
    .profile-page .article-content h3 { font-size: 1.35rem; }
    .profile-page .article-content h4 { font-size: 1.15rem; }
    .profile-page .article-content :is(ul, ol) { padding-left: 1.5rem; margin-bottom: 1rem; }
    .profile-page .article-content li { margin-bottom: .35rem; }
    .profile-page .article-content table { border-collapse: collapse; margin-bottom: 1rem; font-size: 1rem; }
    .profile-page .article-content :is(th, td) { border: 1px solid #dee2e6; padding: .5rem .75rem; vertical-align: top; }
    .profile-page .article-content th { background: #f8f9fa; font-weight: 600; }
    @media (max-width: 575.98px) {
        .profile-page .article-content { font-size: 1rem; }
        .profile-page nav .btn { font-size: .9rem; padding-left: 1rem !important; padding-right: 1rem !important; }
    }
</style>
@endsection
