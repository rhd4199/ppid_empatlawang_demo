@extends('layouts.app')

@section('title', $gallery->title)

@section('content')
<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1>{{ $gallery->title }}</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('galleries.index') }}">Galeri</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($gallery->title, 20) }}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="container pb-5">
    <!-- Album Info -->
    <div class="row mb-5 justify-content-center text-center">
        <div class="col-lg-8">
            <div class="mb-3 text-muted">
                <i class="far fa-calendar-alt me-2"></i> {{ $gallery->created_at->format('d F Y') }}
                <span class="mx-2">|</span>
                <i class="fas fa-camera me-2"></i> {{ $gallery->items->count() }} Foto
            </div>
            @if($gallery->description)
                <p class="lead text-dark">{{ $gallery->description }}</p>
            @endif
        </div>
    </div>

    <!-- Masonry Grid -->
    <div class="row g-3" id="gallery-grid">
        @if($gallery->cover_image)
            <div class="col-md-4 mb-3 gallery-item">
                <div class="position-relative overflow-hidden rounded shadow-sm photo-card cursor-pointer" onclick="openLightbox(0)">
                    <img src="{{ asset('storage/' . $gallery->cover_image) }}" class="img-fluid w-100" alt="Cover">
                    <div class="photo-overlay">
                        <i class="fas fa-search-plus text-white fa-2x"></i>
                    </div>
                </div>
            </div>
        @endif

        @foreach($gallery->items as $index => $item)
        <div class="col-md-4 mb-3 gallery-item">
            <div class="position-relative overflow-hidden rounded shadow-sm photo-card cursor-pointer" onclick="openLightbox({{ $gallery->cover_image ? $index + 1 : $index }})">
                <img src="{{ asset('storage/' . $item->image_path) }}" class="img-fluid w-100" alt="Foto Galeri">
                <div class="photo-overlay">
                    <i class="fas fa-search-plus text-white fa-2x"></i>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    
    <div class="mt-5 text-center">
        <a href="{{ route('galleries.index') }}" class="btn btn-outline-primary rounded-pill px-4">
            <i class="fas fa-arrow-left me-2"></i> Kembali ke Daftar Album
        </a>
    </div>
</div>

<!-- Lightbox Modal -->
<div class="modal fade" id="lightboxModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-fullscreen-lg-down">
        <div class="modal-content bg-transparent border-0">
            <div class="modal-body p-0 position-relative text-center">
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3 z-3" data-bs-dismiss="modal" aria-label="Close"></button>
                
                <div id="lightboxCarousel" class="carousel slide" data-bs-ride="false">
                    <div class="carousel-inner">
                        @if($gallery->cover_image)
                        <div class="carousel-item active">
                            <img src="{{ Str::startsWith($gallery->cover_image, ['http://', 'https://']) ? $gallery->cover_image : asset('storage/' . $gallery->cover_image) }}" class="d-block mx-auto" style="max-height: 90vh; max-width: 100%; object-fit: contain;" alt="Cover">
                        </div>
                        @endif

                        @foreach($gallery->items as $item)
                        <div class="carousel-item {{ !$gallery->cover_image && $loop->first ? 'active' : '' }}">
                            <img src="{{ Str::startsWith($item->image_path, ['http://', 'https://']) ? $item->image_path : asset('storage/' . $item->image_path) }}" class="d-block mx-auto" style="max-height: 90vh; max-width: 100%; object-fit: contain;" alt="Foto Galeri">
                        </div>
                        @endforeach
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#lightboxCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#lightboxCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .photo-card {
        transition: transform 0.3s ease;
        cursor: pointer;
    }
    
    .photo-card:hover {
        transform: translateY(-5px);
    }
    
    .photo-card img {
        transition: transform 0.5s ease;
    }
    
    .photo-card:hover img {
        transform: scale(1.05);
    }
    
    .photo-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .photo-card:hover .photo-overlay {
        opacity: 1;
    }

    /* Masonry Layout using CSS Columns */
    @media (min-width: 768px) {
        #gallery-grid {
            display: block;
            column-count: 2;
            column-gap: 1rem;
        }
    }
    
    @media (min-width: 992px) {
        #gallery-grid {
            column-count: 3;
        }
    }
    
    .gallery-item {
        break-inside: avoid;
        margin-bottom: 1rem;
        width: 100%; /* Important for column layout */
    }
    
    .modal-backdrop.show {
        opacity: 0.9;
    }
    
    .carousel-control-prev, .carousel-control-next {
        width: 5%;
    }
</style>
@endpush

@push('scripts')
<script>
    function openLightbox(index) {
        var myModal = new bootstrap.Modal(document.getElementById('lightboxModal'));
        var carousel = document.getElementById('lightboxCarousel');
        var bsCarousel = new bootstrap.Carousel(carousel);
        
        bsCarousel.to(index);
        myModal.show();
    }
</script>
@endpush
@endsection
