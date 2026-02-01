@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
<style>
    /* Hero Section */
    .hero-section {
        /* Blue gradient matching #0284c7 theme */
        background: linear-gradient(135deg, rgba(2, 132, 199, 0.95) 0%, rgba(3, 105, 161, 0.9) 100%), url('https://upload.wikimedia.org/wikipedia/commons/thumb/1/1a/Kantor_Bupati_Empat_Lawang.jpg/1200px-Kantor_Bupati_Empat_Lawang.jpg');
        background-size: cover;
        background-position: center;
        color: white;
        padding: 60px 0 120px;
        position: relative;
        margin-bottom: 0;
        overflow: hidden;
    }
    .hero-title {
        font-family: 'Playfair Display', serif;
        font-weight: 700;
        font-size: 3.5rem;
        margin-bottom: 20px;
        line-height: 1.2;
    }
    .hero-search {
        background: rgba(255, 255, 255, 0.95);
        padding: 8px;
        border-radius: 50px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        max-width: 600px;
        display: flex;
        backdrop-filter: blur(5px);
    }
    .hero-search input {
        border: none;
        padding: 10px 20px;
        font-size: 1.1rem;
        width: 100%;
        border-radius: 50px 0 0 50px;
        outline: none;
        background: transparent;
    }
    
    /* Animation for Bupati Image */
    .bupati-img-container {
        position: relative;
        z-index: 2;
        animation: slideUp 1s ease-out;
    }
    .bupati-img {
        max-height: 450px;
        filter: drop-shadow(0 10px 20px rgba(0,0,0,0.4));
        transition: transform 0.3s ease;
    }
    .bupati-img:hover {
        transform: scale(1.02);
    }
    @keyframes slideUp {
        from { opacity: 0; transform: translateY(50px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .hero-search button {
        border-radius: 50px;
        padding: 10px 30px;
        font-weight: 600;
        text-transform: uppercase;
    }

    /* Feature Cards */
    .features-container {
        margin-top: -80px;
        position: relative;
        z-index: 10;
        margin-bottom: 60px;
    }
    .feature-card {
        background: white;
        border-radius: 15px;
        padding: 30px;
        text-align: center;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        transition: transform 0.3s, box-shadow 0.3s;
        height: 100%;
        border-bottom: 4px solid var(--primary-color);
    }
    .feature-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.12);
    }
    .feature-icon {
        width: 70px;
        height: 70px;
        background-color: #f0f9ff; /* Light Sky Blue 50 */
        color: var(--primary-color);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        margin: 0 auto 20px;
        transition: 0.3s;
    }
    .feature-card:hover .feature-icon {
        background-color: var(--primary-color);
        color: white;
    }
    .feature-title {
        font-weight: 700;
        margin-bottom: 10px;
        color: var(--text-dark);
    }

    /* News Card */
    .news-card {
        border: none;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        transition: 0.3s;
        height: 100%;
    }
    .news-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    .news-img-wrapper {
        height: 200px;
        overflow: hidden;
        position: relative;
    }
    .news-img-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: 0.5s;
    }
    .news-card:hover .news-img-wrapper img {
        transform: scale(1.1);
    }
    .news-date {
        position: absolute;
        top: 15px;
        left: 15px;
        background: var(--secondary-color);
        color: #000;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    .news-body {
        padding: 20px;
    }
    .news-title {
        font-weight: 700;
        font-size: 1.1rem;
        margin-bottom: 10px;
        line-height: 1.4;
    }
    .news-title a {
        color: var(--text-dark);
        text-decoration: none;
        transition: 0.2s;
    }
    .news-title a:hover {
        color: var(--primary-color);
    }

    /* Stats Section */
    .stats-section {
        background-color: var(--primary-color);
        color: white;
        padding: 60px 0;
        background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%2300695c' fill-opacity='0.2'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }
    .stat-item {
        text-align: center;
        padding: 20px;
    }
    .stat-number {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 5px;
        color: var(--secondary-color);
    }
    .stat-label {
        font-size: 1rem;
        opacity: 0.9;
    }
    /* Gallery Card */
    .gallery-card {
        border-radius: 12px;
        overflow: hidden;
        position: relative;
        height: 250px;
        transition: all 0.3s ease;
    }
    .gallery-card img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    .gallery-card:hover img {
        transform: scale(1.1);
    }
    .gallery-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
        padding: 20px;
        color: white;
    }
</style>
@endpush

@section('title', 'Beranda')

@section('content')
<!-- Hero Section -->
<section class="hero-section d-flex align-items-center">
    <div class="container position-relative z-2">
        <div class="row align-items-center">
            <div class="col-lg-7 text-center mb-5 mb-lg-0">
                <h1 class="hero-title text-white fw-bold mb-3">Layanan Informasi Publik<br>Kabupaten Empat Lawang</h1>
                <p class="lead text-white-50 mb-4 fs-4">Transparan, Akuntabel, dan Melayani Sepenuh Hati</p>
                
                <form action="{{ route('informasi-publik.index') }}" method="GET">
                    <div class="hero-search mx-auto">
                        <input type="text" name="search" placeholder="Cari informasi publik, dokumen, atau regulasi..." aria-label="Search">
                        <button type="submit" class="btn btn-warning rounded-pill px-4 text-dark fw-bold">Cari</button>
                    </div>
                </form>
            </div>
            <div class="col-lg-5 text-center d-none d-lg-block">
                <div class="bupati-img-container">
                    <img src="{{ asset('assets/images/bupati.png') }}" alt="Bupati Empat Lawang" class="img-fluid bupati-img">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Feature Shortcuts -->
<div class="container features-container">
    <div class="row g-4 justify-content-center">
        <div class="col-md-6 col-lg-3">
            <a href="{{ route('request.create') }}" class="text-decoration-none">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-file-signature"></i>
                    </div>
                    <h5 class="feature-title">Permohonan Informasi</h5>
                    <p class="text-muted small mb-0">Ajukan permohonan informasi publik secara online</p>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-3">
            <a href="{{ route('complaint.create') }}" class="text-decoration-none">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-gavel"></i>
                    </div>
                    <h5 class="feature-title">Pengajuan Keberatan</h5>
                    <p class="text-muted small mb-0">Ajukan keberatan atas layanan informasi</p>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-3">
            <a href="#" class="text-decoration-none">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-search-location"></i>
                    </div>
                    <h5 class="feature-title">Cek Status</h5>
                    <p class="text-muted small mb-0">Pantau status permohonan Anda secara real-time</p>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-3">
            <a href="{{ route('informasi-publik.index') }}" class="text-decoration-none">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <h5 class="feature-title">Daftar Informasi</h5>
                    <p class="text-muted small mb-0">Akses dokumen publik yang tersedia setiap saat</p>
                </div>
            </a>
        </div>
    </div>
</div>

<!-- Latest News -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="section-title">
            <a href="{{ route('news.index') }}" style="text-decoration: none"><h2>Berita Terkini</h2></a>
            <p>Informasi terbaru seputar kegiatan Pemerintah Kabupaten Empat Lawang</p>
        </div>

        <div class="row g-4">
            @forelse($news as $item)
            <div class="col-md-6 col-lg-4">
                <div class="news-card bg-white">
                    <div class="news-img-wrapper">
                        <img src="{{ $item->image ? asset('storage/' . $item->image) : 'https://via.placeholder.com/600x400?text=News+Image' }}" alt="{{ $item->title }}">
                        <div class="news-date">{{ $item->created_at->format('d M Y') }}</div>
                    </div>
                    <div class="news-body">
                        <h5 class="news-title">
                            <a href="{{ route('news.show', $item->slug) }}">{{ Str::limit($item->title, 60) }}</a>
                        </h5>
                        <p class="card-text text-muted small">{{ Str::limit(strip_tags($item->content), 100) }}</p>
                        <a href="{{ route('news.show', $item->slug) }}" class="btn btn-link text-primary p-0 text-decoration-none fw-bold">Baca Selengkapnya <i class="fas fa-arrow-right small ms-1"></i></a>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12 text-center py-5">
                <p class="text-muted">Belum ada berita terbaru.</p>
            </div>
            @endforelse
        </div>
        
        <div class="text-center mt-5">
            <a href="{{ route('news.index') }}" class="btn btn-outline-primary rounded-pill px-4">Lihat Semua Berita</a>
        </div>
    </div>
</section>

<!-- Gallery Carousel -->
<section class="py-5">
    <div class="container">
        <div class="section-title text-center mb-5">
            <a href="{{ route('galleries.index') }}" style="text-decoration: none"><h2>Galeri Kegiatan</h2></a>
            <p>Dokumentasi aktivitas dan kegiatan Pemerintah Kabupaten Empat Lawang</p>
        </div>

        @if($galleries->count() > 0)
        <!-- Swiper -->
        <div class="swiper gallerySwiper px-4">
            <div class="swiper-wrapper py-4">
                @foreach($galleries as $gallery)
                <div class="swiper-slide">
                    <a href="{{ route('galleries.show', $gallery->id) }}" class="text-decoration-none">
                        <div class="gallery-card shadow-sm">
                            @if($gallery->cover_image)
                                <img src="{{ asset('storage/' . $gallery->cover_image) }}" alt="{{ $gallery->title }}">
                            @else
                                <div class="bg-secondary text-white d-flex align-items-center justify-content-center h-100" style="min-height: 250px;">
                                    <i class="fas fa-image fa-3x"></i>
                                </div>
                            @endif
                            <div class="gallery-overlay">
                                <h5 class="fw-bold mb-1 text-white">{{ Str::limit($gallery->title, 40) }}</h5>
                                <small class="text-white-50"><i class="fas fa-camera me-1"></i> {{ $gallery->items->count() }} Foto</small>
                            </div>
                        </div>
                    </a>
                </div>
                @endforeach
            </div>
            
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-pagination"></div>
        </div>
        @else
        <div class="text-center py-5">
            <p class="text-muted">Belum ada galeri kegiatan.</p>
        </div>
        @endif
    </div>
</section>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
    var swiper = new Swiper(".gallerySwiper", {
        slidesPerView: 1,
        spaceBetween: 24,
        loop: true,
        autoplay: {
            delay: 3000,
            disableOnInteraction: false,
        },
        pagination: {
            el: ".swiper-pagination",
            clickable: true,
            dynamicBullets: true,
        },
        navigation: {
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev",
        },
        breakpoints: {
            640: {
                slidesPerView: 2,
            },
            1024: {
                slidesPerView: 3,
            },
        },
    });
</script>
@endpush

<!-- Statistics -->
<section class="stats-section">
    <div class="container">
        <div class="row g-4 justify-content-center">
            <div class="col-md-4 col-lg-3">
                <div class="stat-item">
                    <div class="stat-number">1,250</div>
                    <div class="stat-label">Informasi Publik</div>
                </div>
            </div>
            <div class="col-md-4 col-lg-3">
                <div class="stat-item">
                    <div class="stat-number">450</div>
                    <div class="stat-label">Permohonan Selesai</div>
                </div>
            </div>
            <div class="col-md-4 col-lg-3">
                <div class="stat-item">
                    <div class="stat-number">98%</div>
                    <div class="stat-label">Indeks Kepuasan</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="py-5 text-center">
    <div class="container">
        <h3 class="fw-bold mb-3">Butuh Informasi Lebih Lanjut?</h3>
        <p class="text-muted mb-4">Silakan hubungi kami atau datang langsung ke kantor PPID Kabupaten Empat Lawang</p>
        <a href="{{ route('contact.index') }}" class="btn btn-primary rounded-pill px-5 py-2">Hubungi Kami</a>
    </div>
</section>
@endsection
