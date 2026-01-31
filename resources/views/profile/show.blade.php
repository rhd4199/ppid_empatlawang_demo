@extends('layouts.app')

@section('title', $profile->title)

@section('content')
<!-- Page Header -->
<div class="page-header position-relative overflow-hidden">
    <!-- Animated Shapes Background -->
    <div class="position-absolute top-0 start-0 w-100 h-100 overflow-hidden" style="z-index: 0;">
        <div class="shape shape-1" style="opacity: 0.1;"></div>
        <div class="shape shape-2" style="opacity: 0.1;"></div>
    </div>
    
    <div class="container position-relative" style="z-index: 1;">
        <div class="row justify-content-center">
            <div class="col-md-10 text-center">
                <h1 class="display-4 fw-bold mb-3">{{ $profile->title }}</h1>
                <nav aria-label="breadcrumb" class="d-flex justify-content-center">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-white text-opacity-75 text-decoration-none">Beranda</a></li>
                        <li class="breadcrumb-item text-white text-opacity-75">Profil</li>
                        <li class="breadcrumb-item active text-white" aria-current="page">{{ $profile->title }}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="container pb-5 mt-n5 position-relative" style="z-index: 2;">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                @if($profile->image)
                    @if ($profile->type == 'struktur')
                        <div class="position-relative">
                            <img src="{{ asset('storage/' . $profile->image) }}" class="img-fluid w-100 object-fit-cover"  alt="{{ $profile->title }}">
                            <div class="position-absolute bottom-0 start-0 w-100 bg-gradient-to-t from-black-50 p-4"></div>
                        </div>    
                    @else
                        <div class="position-relative">
                            <img src="{{ asset('storage/' . $profile->image) }}" class="img-fluid w-100 object-fit-cover" style="max-height: 500px;" alt="{{ $profile->title }}">
                            <div class="position-absolute bottom-0 start-0 w-100 bg-gradient-to-t from-black-50 p-4"></div>
                        </div>
                    @endif
                @endif
                
                <div class="card-body p-4 p-md-5 bg-white">
                    <div class="article-content" style="font-size: 1.1rem; line-height: 1.8; color: #444;">
                        {!! $profile->content !!}
                    </div>
                </div>
            </div>
            
            <div class="mt-5 text-center">
                <a href="{{ route('home') }}" class="btn btn-outline-primary rounded-pill px-4 py-2 fw-bold hover-scale transition-all">
                    <i class="fas fa-arrow-left me-2"></i> Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    .mt-n5 { margin-top: -3rem; }
    .bg-gradient-to-t { background: linear-gradient(to top, rgba(0,0,0,0.5), transparent); }
    .hover-scale:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
    .transition-all { transition: all 0.3s ease; }
    
    /* Reuse animated shapes from contact page but simpler */
    .shape { position: absolute; border-radius: 50%; filter: blur(80px); }
    .shape-1 { background: #ffffff; width: 400px; height: 400px; top: -100px; right: -100px; }
    .shape-2 { background: #ffffff; width: 300px; height: 300px; bottom: -50px; left: -100px; }
</style>
@endsection
