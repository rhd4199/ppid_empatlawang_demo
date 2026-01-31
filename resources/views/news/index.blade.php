@extends('layouts.app')

@section('title', 'Berita & Artikel')

@section('content')
<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1>Berita & Artikel</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Berita</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="container pb-5">
    @if($news->count() > 0 || (isset($headline) && $headline))
    @php
        // Determine the featured news: either the explicit headline or the latest news (fallback)
        $featured = $headline ?? ($news->onFirstPage() ? $news->first() : null);
    @endphp

    @if($featured)
        <!-- Featured News (Headline or Latest) -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="card border-0 shadow-sm overflow-hidden text-white card-hover featured-card">
                    <div class="row g-0 h-100">
                        <div class="col-md-8 position-relative">
                            @if($featured->image)
                                <img src="{{ asset('storage/' . $featured->image) }}" class="img-fluid w-100 h-100 object-fit-cover" alt="{{ $featured->title }}" style="min-height: 400px;">
                            @else
                                <div class="bg-secondary w-100 h-100 d-flex align-items-center justify-content-center" style="min-height: 400px;">
                                    <i class="fas fa-newspaper fa-5x opacity-50"></i>
                                </div>
                            @endif
                            <div class="overlay-gradient"></div>
                        </div>
                        <div class="col-md-4 bg-dark d-flex align-items-center">
                            <div class="card-body p-4 p-lg-5">
                                <span class="badge bg-warning text-dark mb-3">Berita Utama</span>
                                <h2 class="card-title fw-bold mb-3 font-playfair">{{ $featured->title }}</h2>
                                <div class="mb-3 text-white-50 small">
                                    <i class="far fa-calendar-alt me-2"></i> {{ $featured->published_at ? \Carbon\Carbon::parse($featured->published_at)->isoFormat('D MMMM Y') : '-' }}
                                    <span class="mx-2">|</span>
                                    <i class="far fa-user me-2"></i> {{ $featured->author ?? 'Admin' }}
                                </div>
                                <p class="card-text text-white-50 mb-4">{{ Str::limit(strip_tags($featured->content), 150) }}</p>
                                <a href="{{ route('news.show', $featured->slug) }}" class="btn btn-outline-light stretched-link rounded-pill px-4">Baca Selengkapnya</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- News Grid -->
    <div class="row g-4">
        @foreach($news as $item)
            {{-- If we used the first item as fallback headline (no explicit headline), skip it here --}}
            @if(!$headline && $loop->first && $news->onFirstPage()) @continue @endif
            
            <div class="col-md-6 col-lg-4">
                    <article class="card h-100 border-0 shadow-sm card-hover hover-lift">
                        <div class="card-img-wrapper position-relative overflow-hidden">
                            @if($item->image)
                                <img src="{{ asset('storage/' . $item->image) }}" class="card-img-top" alt="{{ $item->title }}">
                            @else
                                <div class="bg-light text-muted d-flex align-items-center justify-content-center card-img-top">
                                    <i class="fas fa-newspaper fa-3x opacity-25"></i>
                                </div>
                            @endif
                            <div class="card-date-badge">
                                <span class="day">{{ $item->published_at ? \Carbon\Carbon::parse($item->published_at)->format('d') : '-' }}</span>
                                <span class="month">{{ $item->published_at ? \Carbon\Carbon::parse($item->published_at)->format('M') : '-' }}</span>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-2 text-muted small">
                                <i class="far fa-user me-1 text-primary"></i> {{ $item->author ?? 'Admin' }}
                            </div>
                            <h5 class="card-title font-playfair fw-bold mb-3">
                                <a href="{{ route('news.show', $item->slug) }}" class="text-dark text-decoration-none stretched-link">
                                    {{ Str::limit($item->title, 60) }}
                                </a>
                            </h5>
                            <p class="card-text text-muted small mb-0">
                                {{ Str::limit(strip_tags($item->content), 100) }}
                            </p>
                        </div>
                        <div class="card-footer bg-white border-top-0 p-4 pt-0">
                            <a href="{{ route('news.show', $item->slug) }}" class="text-primary text-decoration-none fw-bold small read-more-link">
                                Baca Artikel <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </article>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-5">
            {{ $news->links() }}
        </div>

    @else
        <div class="text-center py-5">
            <div class="mb-3">
                <i class="far fa-newspaper fa-4x text-muted opacity-25"></i>
            </div>
            <h4 class="text-muted">Belum ada berita yang dipublikasikan.</h4>
        </div>
    @endif
</div>

@endsection

@push('styles')
<style>
    .font-playfair {
        font-family: 'Playfair Display', serif;
    }
    
    .card-img-wrapper {
        height: 240px;
    }
    
    .card-img-top {
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    
    .hover-lift:hover {
        transform: translateY(-5px);
    }
    
    .hover-lift:hover .card-img-top {
        transform: scale(1.05);
    }
    
    .card-date-badge {
        position: absolute;
        top: 20px;
        left: 20px;
        background: white;
        padding: 8px 15px;
        border-radius: 8px;
        text-align: center;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        z-index: 2;
    }
    
    .card-date-badge .day {
        display: block;
        font-weight: 700;
        font-size: 1.2rem;
        line-height: 1;
        color: var(--primary-color);
    }
    
    .card-date-badge .month {
        display: block;
        font-size: 0.75rem;
        text-transform: uppercase;
        font-weight: 600;
        color: #6c757d;
    }
    
    .read-more-link i {
        transition: transform 0.3s ease;
    }
    
    .read-more-link:hover i {
        transform: translateX(5px);
    }

    .overlay-gradient {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(to right, rgba(0,0,0,0.3) 0%, rgba(0,0,0,0) 100%);
        pointer-events: none;
    }

    /* Featured Card Specifics */
    .featured-card .object-fit-cover {
        object-fit: cover;
    }
    
    @media (max-width: 768px) {
        .card-img-wrapper {
            height: 200px;
        }
    }
</style>
@endpush
