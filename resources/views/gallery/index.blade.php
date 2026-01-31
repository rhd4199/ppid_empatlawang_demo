@extends('layouts.app')

@section('title', 'Galeri Kegiatan')

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
    <div class="row g-4">
        @forelse($galleries as $gallery)
        <div class="col-md-6 col-lg-4">
            <a href="{{ route('galleries.show', $gallery->id) }}" class="text-decoration-none gallery-card-link">
                <div class="card h-100 border-0 shadow-sm gallery-card overflow-hidden">
                    <div class="gallery-img-wrapper position-relative">
                        @if($gallery->cover_image)
                            <img src="{{ Str::startsWith($gallery->cover_image, ['http://', 'https://']) ? $gallery->cover_image : asset('storage/' . $gallery->cover_image) }}" class="card-img-top" alt="{{ $gallery->title }}">
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
                                <div class="text-white fw-bold">Lihat Album</div>
                            </div>
                        </div>
                        <div class="photo-count-badge">
                            <i class="fas fa-camera me-1"></i> {{ $gallery->items->count() }} Foto
                        </div>
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
        font-family: 'Playfair Display', serif;
    }
    
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
