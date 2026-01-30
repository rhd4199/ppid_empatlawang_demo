<!doctype html>
<html lang="id">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'PPID Kabupaten Empat Lawang')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .navbar-brand { font-weight: bold; }
        footer { background: #f8f9fa; padding: 20px 0; margin-top: 50px; }
        .hero-section { background: #0056b3; color: white; padding: 60px 0; margin-bottom: 30px; }
    </style>
    @stack('styles')
  </head>
  <body>
    
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
      <div class="container">
        <a class="navbar-brand" href="{{ route('home') }}">
            <i class="fas fa-university me-2"></i>PPID Empat Lawang
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav ms-auto">
            <li class="nav-item"><a class="nav-link" href="{{ route('home') }}">Beranda</a></li>
            
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Profil</a>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="{{ route('profile.index') }}">Profil Pemerintah</a></li>
                <li><a class="dropdown-item" href="{{ route('profile.show', 'visi-misi') }}">Visi & Misi</a></li>
                <li><a class="dropdown-item" href="{{ route('profile.show', 'struktur') }}">Struktur Organisasi</a></li>
              </ul>
            </li>

            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Informasi Publik</a>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="{{ route('informasi-publik.index', ['category' => 'berkala']) }}">Berkala</a></li>
                <li><a class="dropdown-item" href="{{ route('informasi-publik.index', ['category' => 'serta_merta']) }}">Serta Merta</a></li>
                <li><a class="dropdown-item" href="{{ route('informasi-publik.index', ['category' => 'setiap_saat']) }}">Setiap Saat</a></li>
                <li><a class="dropdown-item" href="{{ route('informasi-publik.index', ['category' => 'dikecualikan']) }}">Dikecualikan</a></li>
              </ul>
            </li>

            <li class="nav-item"><a class="nav-link" href="{{ route('standar-layanan.index') }}">Standar Layanan</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('laporan.index') }}">Laporan</a></li>
            
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Info</a>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="{{ route('berita.index') }}">Berita</a></li>
                <li><a class="dropdown-item" href="{{ route('galeri.index') }}">Galeri</a></li>
                <li><a class="dropdown-item" href="{{ route('event.index') }}">Event</a></li>
              </ul>
            </li>

            <li class="nav-item"><a class="nav-link" href="{{ route('pengadaan.index') }}">Pengadaan</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('contact.index') }}">Kontak</a></li>
            
            <li class="nav-item ms-2">
                <a href="{{ route('request.create') }}" class="btn btn-warning text-dark fw-bold">Permohonan Info</a>
            </li>
          </ul>
        </div>
      </div>
    </nav>

    <div class="container mt-4 min-vh-100">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>

    <footer class="text-center text-lg-start bg-light text-muted">
        <div class="container p-4">
            <div class="row">
                <div class="col-lg-6 col-md-12 mb-4 mb-md-0">
                    <h5 class="text-uppercase">PPID Kabupaten Empat Lawang</h5>
                    <p>
                        Melayani Sepenuh Hati, Transparan, dan Akuntabel.
                        Portal Resmi Pejabat Pengelola Informasi dan Dokumentasi.
                    </p>
                </div>
                <div class="col-lg-3 col-md-6 mb-4 mb-md-0">
                    <h5 class="text-uppercase">Tautan Cepat</h5>
                    <ul class="list-unstyled mb-0">
                        <li><a href="{{ route('home') }}" class="text-dark">Beranda</a></li>
                        <li><a href="{{ route('request.create') }}" class="text-dark">Permohonan Informasi</a></li>
                        <li><a href="{{ route('complaint.create') }}" class="text-dark">Pengajuan Keberatan</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6 mb-4 mb-md-0">
                    <h5 class="text-uppercase">Kontak</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-map-marker-alt me-2"></i> Kantor Bupati Empat Lawang</li>
                        <li><i class="fas fa-envelope me-2"></i> ppid@empatlawangkab.go.id</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="text-center p-3 bg-dark text-white">
            © 2026 Copyright:
            <a class="text-white" href="#">PPID Kabupaten Empat Lawang</a>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    @stack('scripts')
  </body>
</html>
