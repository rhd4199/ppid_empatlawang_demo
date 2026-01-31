@extends('layouts.app')

@section('title', $news->title)

@section('content')
<!-- Custom Header for Article -->
<div class="position-relative bg-dark text-white" style="min-height: 400px;">
    @if($news->image)
        <img src="{{ asset('storage/' . $news->image) }}" class="w-100 h-100 position-absolute top-0 start-0" style="object-fit: cover; opacity: 0.4;" alt="{{ $news->title }}">
    @else
        <div class="position-absolute top-0 start-0 w-100 h-100 bg-secondary" style="opacity: 0.4;"></div>
    @endif
    
    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(to bottom, rgba(0,0,0,0.3) 0%, rgba(0,0,0,0.8) 100%);"></div>

    <div class="container position-relative h-100 d-flex flex-column justify-content-end pb-5 pt-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <nav aria-label="breadcrumb" class="mb-3">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-white-50 text-decoration-none">Beranda</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('news.index') }}" class="text-white-50 text-decoration-none">Berita</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">Baca Artikel</li>
                    </ol>
                </nav>
                <h1 class="display-4 fw-bold font-playfair mb-3">{{ $news->title }}</h1>
                <div class="d-flex align-items-center text-white-50">
                    <div class="d-flex align-items-center me-4">
                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white me-2" style="width: 40px; height: 40px;">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <div class="small text-uppercase fw-bold text-light" style="font-size: 0.7rem;">Penulis</div>
                            <div class="fw-medium">{{ $news->author ?? 'Admin PPID' }}</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center text-white me-2" style="width: 40px; height: 40px;">
                            <i class="far fa-calendar-alt"></i>
                        </div>
                        <div>
                            <div class="small text-uppercase fw-bold text-light" style="font-size: 0.7rem;">Tanggal</div>
                            <div class="fw-medium">{{ $news->published_at ? \Carbon\Carbon::parse($news->published_at)->isoFormat('D MMMM Y') : '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <article class="blog-post">
                <div class="article-content fs-5 lh-lg mb-5 text-dark">
                    {!! $news->content !!}
                </div>

                <div class="border-top border-bottom py-4 my-5 d-flex justify-content-between align-items-center">
                    <div class="fw-bold text-muted">Bagikan Artikel:</div>
                    <div class="d-flex gap-2">
                        <a href="#" class="btn btn-outline-primary rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="btn btn-outline-info rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="btn btn-outline-success rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;"><i class="fab fa-whatsapp"></i></a>
                        <button class="btn btn-outline-secondary rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;" onclick="window.print()"><i class="fas fa-print"></i></button>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('news.index') }}" class="btn btn-outline-dark rounded-pill px-4">
                        <i class="fas fa-arrow-left me-2"></i> Kembali ke Berita
                    </a>
                </div>
            </article>
        </div>
    </div>
</div>

@push('styles')
<style>
    .font-playfair {
        font-family: 'Playfair Display', serif;
    }
    
    .article-content {
        font-family: 'Georgia', serif;
        color: #333;
    }
    
    .article-content p {
        margin-bottom: 1.5rem;
    }
    
    .article-content img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        margin: 20px 0;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }
    
    .article-content h2, .article-content h3 {
        font-family: 'Inter', sans-serif;
        font-weight: 700;
        margin-top: 2.5rem;
        margin-bottom: 1rem;
        color: var(--primary-color);
    }
    
    .article-content blockquote {
        border-left: 4px solid var(--secondary-color);
        padding-left: 20px;
        margin: 30px 0;
        font-style: italic;
        color: #555;
        font-size: 1.25rem;
    }
</style>
@endpush
@endsection
