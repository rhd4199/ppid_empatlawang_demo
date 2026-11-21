@extends('layouts.app')

@section('title', 'Galeri Kegiatan')
@section('description', 'Galeri foto dan video dokumentasi kegiatan PPID dan Pemerintah Kabupaten Empat Lawang.')

@section('content')
<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1>Galeri Kegiatan</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Galeri</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="container pb-5">
    <ul class="nav nav-pills justify-content-center gap-2 mb-4" aria-label="Filter galeri">
        @foreach(['' => ['Semua', 'fa-th-large'], 'foto' => ['Foto', 'fa-camera'], 'video' => ['Video', 'fa-play-circle']] as $key => [$label, $icon])
            <li class="nav-item">
                <a class="nav-link rounded-pill px-4 {{ ($type ? ($type === 'photo' ? 'foto' : 'video') : '') === $key ? 'active' : '' }}" href="{{ route('galleries.index', array_filter(['tipe' => $key])) }}"><i class="fas {{ $icon }} me-1"></i> {{ $label }}</a>
            </li>
        @endforeach
    </ul>

    <div class="row g-4">
        @forelse($galleries as $gallery)
        <div class="col-md-6 col-lg-4">
            <a href="{{ route('galleries.show', $gallery->slug) }}" class="text-decoration-none gallery-card-link">
                <div class="card h-100 border-0 shadow-sm gallery-card overflow-hidden">
                    <div class="gallery-img-wrapper position-relative">
                        @if($gallery->cover_image_url)
                            <img src="{{ $gallery->cover_image_url }}" class="card-img-top" alt="{{ $gallery->title }}" loading="lazy">
                        @else
                            <div class="bg-light text-muted d-flex align-items-center justify-content-center card-img-top">
                                <i class="fas fa-images fa-3x opacity-25"></i>
                            </div>
                        @endif
                        <div class="gallery-overlay">
                            <div class="text-center">
                                <span class="btn btn-light rounded-circle shadow-lg mb-2" style="width: 50px; height: 50px; display: inline-flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-search-plus text-primary"></i>
                                </span>
                                <div class="text-white fw-bold">{{ $gallery->youtube_id ? 'Tonton Video' : 'Lihat Album' }}</div>
                            </div>
                        </div>
                        @if($gallery->youtube_id)
                            <span class="video-play-icon" aria-hidden="true"><i class="fas fa-play"></i></span>
                            <div class="photo-count-badge"><i class="fas fa-video me-1"></i> Video</div>
                        @else
                            <div class="photo-count-badge">
                                <i class="fas fa-camera me-1"></i> {{ $gallery->items_count }} Foto
                            </div>
                        @endif
                    </div>
                    <div class="card-body p-4 text-center">
                        <h5 class="card-title font-playfair fw-bold text-dark mb-2">{{ $gallery->title }}</h5>
                        <p class="card-text text-muted small mb-0">
                            <i class="far fa-calendar-alt me-1 text-primary"></i> {{ $gallery->created_at->format('d F Y') }}
                        </p>
                        @if($gallery->description)
                        <p class="card-text text-muted small mt-2 text-truncate">{{ $gallery->description }}</p>
                        @endif
                    </div>
                </div>
            </a>
        </div>
        @empty
        <div class="col-12">
            <div class="text-center py-5">
                <div class="mb-3">
                    <i class="far fa-images fa-4x text-muted opacity-25"></i>
                </div>
                <h4 class="text-muted">Belum ada album galeri yang diunggah.</h4>
            </div>
        </div>
        @endforelse
    </div>

    <div class="mt-5 d-flex justify-content-center">
        {{ $galleries->links() }}
    </div>
</div>

@push('styles')
<style>
    .font-playfair {
        font-family: 'Plus Jakarta Sans', sans-serif;
    }
    
    .video-play-icon {
        position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 1;
        width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
        background: rgba(220, 38, 38, .9); color: #fff; font-size: 1.5rem; box-shadow: 0 6px 18px rgba(0,0,0,.35); pointer-events: none;
    }
    .video-play-icon i { margin-left: 4px; }
    .gallery-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .gallery-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
    }
    
    .gallery-img-wrapper {
        height: 250px;
        position: relative;
        overflow: hidden;
    }
    
    .card-img-top {
        height: 100%;
        width: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    
    .gallery-card:hover .card-img-top {
        transform: scale(1.1);
    }
    
    .gallery-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 77, 64, 0.7); /* Primary color with opacity */
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .gallery-card:hover .gallery-overlay {
        opacity: 1;
    }
    
    .photo-count-badge {
        position: absolute;
        bottom: 10px;
        right: 10px;
        background: rgba(0,0,0,0.6);
        color: white;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        backdrop-filter: blur(4px);
    }
</style>
@endpush
@endsection
