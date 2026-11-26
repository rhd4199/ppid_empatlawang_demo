<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{-- Always light, even when the browser/OS is dark ("only" also stops Chrome's auto dark mode). --}}
    <meta name="color-scheme" content="only light">
    <style>:root { color-scheme: only light; }</style>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $siteName = 'PPID Kabupaten Empat Lawang';
        // Inline sections are stored HTML-escaped; decode so the echoes below escape only once.
        $section = fn (string $name) => html_entity_decode(trim($__env->yieldContent($name)), ENT_QUOTES | ENT_HTML5);
        $pageTitle = $section('title');
        $fullTitle = $pageTitle !== '' && $pageTitle !== $siteName ? $pageTitle.' | '.$siteName : $siteName;
        $metaDescription = $section('description') ?: 'PPID Kabupaten Empat Lawang: informasi publik, permohonan informasi, pengajuan keberatan, berita, dan laporan layanan.';
        // Only query params that select distinct content stay in the canonical URL.
        $canonicalQuery = http_build_query(array_filter(request()->only(['category', 'tab', 'page']), fn ($v) => is_string($v) && $v !== '' && $v !== '1'));
        $canonicalUrl = url()->current().($canonicalQuery ? '?'.$canonicalQuery : '');
        $ogImage = $section('og_image') ?: asset('assets/images/Lambang_Empat_Lawang.png');
    @endphp
    <title>{{ $fullTitle }}</title>
    <meta name="description" content="{{ Str::limit($metaDescription, 160) }}">
    <meta name="robots" content="@yield('robots', 'index, follow')">
    <link rel="canonical" href="{{ $canonicalUrl }}">

    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:locale" content="id_ID">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:title" content="{{ $pageTitle ?: $siteName }}">
    <meta property="og:description" content="{{ Str::limit($metaDescription, 200) }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta name="twitter:card" content="{{ $section('og_image') ? 'summary_large_image' : 'summary' }}">

    @if(request()->routeIs('home'))
        @php
            // Built in @php: Blade would compile the "@context" key as its @context directive.
            $orgJsonLd = [
                '@context' => 'https://schema.org',
                '@graph' => [
                    ['@type' => 'WebSite', '@id' => url('/').'#website', 'url' => url('/'), 'name' => $siteName, 'inLanguage' => 'id-ID', 'publisher' => ['@id' => url('/').'#organization']],
                    array_filter([
                        '@type' => 'GovernmentOrganization',
                        '@id' => url('/').'#organization',
                        'name' => $siteName,
                        'url' => url('/'),
                        'logo' => asset('assets/images/Lambang_Empat_Lawang.png'),
                        'address' => $contactSettings?->address ? plain_text($contactSettings->address) : null,
                        'telephone' => $contactSettings?->phones[0] ?? null,
                        'email' => $contactSettings?->emails[0] ?? null,
                        'sameAs' => array_values(array_filter(array_column($contactSettings?->social_media ?? [], 'url'), fn ($u) => str_starts_with((string) $u, 'http'))) ?: null,
                    ]),
                ],
            ];
        @endphp
        <script type="application/ld+json">{!! json_encode($orgJsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
    @endif

    @stack('meta')

    <link rel="icon" type="image/png" href="{{ asset('assets/images/Lambang_Empat_Lawang.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('assets/images/Lambang_Empat_Lawang.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        /* WYSIWYG output: keep pasted images/tables/embeds/long URLs inside their container */
        :is(.rich-text, .article-content) { overflow-wrap: anywhere; }
        :is(.rich-text, .article-content) :is(img, video, iframe) { max-width: 100%; height: auto; }
        :is(.rich-text, .article-content) iframe { aspect-ratio: 16 / 9; }
        :is(.rich-text, .article-content) table { display: block; max-width: 100%; overflow-x: auto; }
        .rich-text > :last-child { margin-bottom: 0; }
        :root {
            --primary-color: #0284c7; /* Sky Blue 600 */
            --secondary-color: #ffc107; /* Gold/Yellow for accents */
            --accent-color: #0369a1; /* Sky Blue 700 */
            --text-dark: #212529;
            --text-light: #6c757d;
            --bg-light: #f8f9fa;

            /* Bootstrap Variables */
            --bs-primary: #0284c7;
            --bs-primary-rgb: 2, 132, 199;
            --bs-secondary: #ffc107;
            --bs-secondary-rgb: 255, 193, 7;
        }

        /* Bootstrap Overrides */

        .btn-primary { 
            background-color: var(--primary-color) !important; 
            border-color: var(--primary-color) !important; 
        }
        .btn-primary:hover { 
            background-color: var(--accent-color) !important; 
            border-color: var(--accent-color) !important; 
        }
        .btn-outline-primary {
            color: var(--primary-color) !important;
            border-color: var(--primary-color) !important;
        }
        .btn-outline-primary:hover {
            background-color: var(--primary-color) !important;
            color: white !important;
        }
        .border-primary { border-color: var(--primary-color) !important; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--text-dark);
            background-color: #f9fbfd; /* Very subtle blue-ish grey */
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        main {
            flex: 1;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--primary-color);
        }

        /* Page Header */
        .page-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #00251a 100%);
            color: white;
            padding: 60px 0;
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
        }
        .page-header::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjIiIGZpbGw9IiNmZmZmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSIvPjwvc3ZnPg=='); /* Subtle dot pattern */
            opacity: 0.3;
        }
        .page-header h1 {
            color: white;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .page-header .breadcrumb {
            background: transparent;
            padding: 0;
            margin: 0;
        }
        .page-header .breadcrumb-item {
            color: rgba(255,255,255,0.7);
            font-size: 0.9rem;
        }
        .page-header .breadcrumb-item.active {
            color: white;
        }
        .page-header .breadcrumb-item + .breadcrumb-item::before {
            color: rgba(255,255,255,0.5);
        }
        .page-header a {
            color: rgba(255,255,255,0.9);
            text-decoration: none;
        }
        .page-header a:hover {
            color: var(--secondary-color);
        }

        /* Navbar social icons — compact circles, replaces the old top-bar strip */
        .nav-social a {
            width: 28px;
            height: 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: rgba(2, 132, 199, 0.08);
            color: var(--primary-color);
            font-size: 0.75rem;
            transition: 0.2s;
        }
        .nav-social a:hover {
            background: var(--primary-color);
            color: #fff;
        }
        /* Desktop: float the icons on the left edge so the navbar stays short; collapsed menu keeps them inline */
        @media (min-width: 1200px) {
            .nav-social {
                position: fixed;
                left: 0;
                top: 50%;
                transform: translateY(-50%);
                z-index: 1070;
                flex-direction: column;
                gap: 6px !important;
                margin: 0 !important;
                padding: 10px 8px;
                background: #fff;
                border-radius: 0 14px 14px 0;
                box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
            }
            .nav-social a {
                width: 34px;
                height: 34px;
                font-size: 0.9rem;
            }
        }

        /* Navbar */
        .navbar {
            background-color: white;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08); /* Sedikit lebih soft shadow-nya */
            padding: 12px 0;
            transition: all 0.3s ease;
        }
        .navbar-brand img {
            height: 38px;
            margin-right: 12px;
        }
        .navbar-brand {
            display: flex;
            align-items: center;
            color: var(--primary-color);
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 700;
        }
        
        /* Navbar Links - Modern Pill Style */
        .navbar-nav .nav-link {
            color: var(--text-dark);
            font-weight: 500;
            padding: 8px 10px !important;
            margin: 0;
            border-radius: 50px; /* Pill Shape */
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            font-size: 0.82rem;
            white-space: nowrap;
        }

        /* Hover & Active State (Unified) */
        .navbar-nav .nav-link:hover, 
        .navbar-nav .nav-link.active,
        .navbar-nav .show > .nav-link {
            color: white !important;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
            box-shadow: 0 4px 10px rgba(0, 77, 64, 0.2);
            font-weight: 600;
            transform: translateY(-1px);
        }
        
        /* Remove old underline */
        .navbar-nav .nav-link.active::after {
            content: none;
        }

        /* Dropdown Menu Styling */
        .dropdown-menu {
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border-radius: 12px;
            margin-top: 10px !important;
            padding: 8px;
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); } 
            to { opacity: 1; transform: translateY(0); }
        }

        .dropdown-item {
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.2s;
            margin-bottom: 2px;
        }

        .dropdown-item:hover {
            background-color: rgba(0, 77, 64, 0.08); /* Very light primary */
            color: var(--primary-color);
            transform: translateX(3px);
        }
        
        /* Dropdown Active State */
        .dropdown-item.active, .dropdown-item:active {
            background-color: var(--primary-color);
            color: white;
            font-weight: 500;
        }
        
        /* Make dropdown toggle active when child is active */
        .navbar-nav .dropdown-toggle.active {
            /* Handled by .nav-link.active above */
        }

        /* Mobile (navbar collapsed below xl) */
        @media (max-width: 1199.98px) {
            /* Collapsed menu is taller than a phone screen; sticky navbar would hide the rest */
            .navbar-collapse {
                max-height: calc(100vh - 70px);
                max-height: calc(100dvh - 70px);
                overflow-y: auto;
            }
        }
        @media (max-width: 767.98px) {
            /* Fixed bottom bar (partials/assistant) covers the last 64px; keep the last menu items reachable */
            .navbar-collapse {
                max-height: calc(100vh - 134px);
                max-height: calc(100dvh - 134px);
            }
            .navbar-nav { align-items: stretch !important; }
            .navbar-nav .nav-link {
                padding: 12px 16px !important;
                font-size: 0.95rem;
                white-space: normal;
            }
            .dropdown-item {
                padding: 10px 16px;
                white-space: normal;
            }
        }
        @media (max-width: 575.98px) {
            /* Wide logo (7.7:1) must leave room for the toggler on 360px screens */
            .navbar-brand img {
                height: auto !important;
                max-height: 45px;
                max-width: calc(100vw - 140px);
            }
        }

        /* Footer */
        footer {
            background-color: #1a1a1a;
            color: #bbb;
            margin-top: auto;
            padding-top: 60px;
        }
        footer h5 {
            color: white;
            font-weight: 600;
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 10px;
        }
        footer h5::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 40px;
            height: 3px;
            background-color: var(--secondary-color);
        }
        footer a {
            color: #bbb;
            text-decoration: none;
            transition: 0.3s;
            display: block;
            margin-bottom: 10px;
        }
        footer a:hover {
            color: var(--secondary-color);
            padding-left: 5px;
        }
        .footer-bottom {
            background-color: #111;
            padding: 20px 0;
            margin-top: 50px;
            font-size: 0.9rem;
        }

        /* Utilities */
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        .btn-primary:hover {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
        }
        .section-title {
            text-align: center;
            margin-bottom: 40px;
        }
        .section-title h2 {
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        .section-title p {
            color: var(--text-light);
            max-width: 600px;
            margin: 0 auto;
        }

        /* Card Hover */
        .card-hover {
            transition: transform 0.3s, box-shadow 0.3s;
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        /* Nav Pills */
        .nav-pills .nav-link {
            color: var(--text-light);
            font-weight: 500;
            padding: 10px 20px;
            border-radius: 50px;
            transition: 0.3s;
        }
        .nav-pills .nav-link.active, .nav-pills .nav-link:hover {
            background-color: var(--primary-color);
            color: white;
        }

        /* Table */
        .table-custom thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #e9ecef;
            color: var(--primary-color);
            font-weight: 600;
        }
        .table-custom tbody tr:hover {
            background-color: #f1f3f5;
        }
    </style>
    @stack('styles')
</head>
<body>

    <a href="#konten-utama" class="a11y-skip">Lewati ke konten utama</a>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-xl sticky-top">
        <div class="container-fluid px-3 px-sm-4 px-lg-5">
            <a class="navbar-brand" href="{{ url('/') }}">
                <img src="{{ asset('assets/images/logo-with_name.png') }}" alt="PPID Kabupaten Empat Lawang" style="height: 45px;">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('/') ? 'active' : '' }}" href="{{ url('/') }}">Beranda</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('profiles.*') || request()->routeIs('officials.*') || request()->routeIs('agencies.*') ? 'active' : '' }}" href="#" role="button" data-bs-toggle="dropdown">Profil</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="https://empatlawangkab.go.id/" target="_blank">Pemkab Empat Lawang</a></li>
                            <li><a class="dropdown-item" href="{{ route('profiles.show', 'tentang-ppid') }}">Tentang PPID</a></li>
                            <li><a class="dropdown-item" href="{{ route('profiles.show', 'visi-misi') }}">Visi & Misi</a></li>
                            <li><a class="dropdown-item" href="{{ route('profiles.show', 'struktur-organisasi') }}">Struktur Organisasi</a></li>
                            <li><a class="dropdown-item" href="{{ route('profiles.show', 'tugas-fungsi') }}">Tugas & Fungsi</a></li>
                            <li><a class="dropdown-item {{ request()->routeIs('officials.*') ? 'active' : '' }}" href="{{ route('officials.index') }}">Pejabat & Pejabat Struktural</a></li>
                            <li><a class="dropdown-item {{ request()->routeIs('agencies.*') ? 'active' : '' }}" href="{{ route('agencies.index') }}">Perangkat Daerah (OPD) & Kecamatan</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('informasi-publik.*') ? 'active' : '' }}" href="#" role="button" data-bs-toggle="dropdown">Informasi Publik</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('informasi-publik.index', ['category' => 'berkala']) }}">Informasi Berkala</a></li>
                            <li><a class="dropdown-item" href="{{ route('informasi-publik.index', ['category' => 'berkala', 'search' => 'LHKPN']) }}">LHKPN Pimpinan</a></li>
                            <li><a class="dropdown-item" href="{{ route('informasi-publik.index', ['category' => 'serta-merta']) }}">Informasi Serta Merta</a></li>
                            <li><a class="dropdown-item" href="{{ route('informasi-publik.index', ['category' => 'setiap-saat']) }}">Informasi Setiap Saat</a></li>
                            <li><a class="dropdown-item" href="{{ route('informasi-publik.index') }}">Daftar Informasi Publik</a></li>
                            <li><a class="dropdown-item" href="{{ route('informasi-publik.index', ['category' => 'dikecualikan']) }}">Daftar Informasi Dikecualikan</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('standard-service.*', 'service-requirements.*') ? 'active' : '' }}" href="#" role="button" data-bs-toggle="dropdown">Standar Layanan</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item fw-semibold" href="{{ route('service-requirements.index') }}"><i class="fas fa-clipboard-list me-1 text-primary"></i> Persyaratan Layanan (KTP, SIM, Tanah, Izin)</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('standard-service.index') }}#alur">Alur Layanan Informasi Publik</a></li>
                            <li><a class="dropdown-item" href="{{ route('standard-service.index') }}#tata-cara">Tata Cara Permohonan Informasi</a></li>
                            <li><a class="dropdown-item" href="{{ route('standard-service.index') }}#permohonan">Form Permohonan Informasi Publik</a></li>
                            <li><a class="dropdown-item" href="{{ route('standard-service.index') }}#keberatan">Tata Cara Pengajuan Keberatan</a></li>
                            <li><a class="dropdown-item" href="{{ route('standard-service.index') }}#sengketa">Tata Cara Penyelesaian Sengketa</a></li>
                            <li><a class="dropdown-item" href="{{ route('standard-service.index') }}#pengaduan">Tata Cara Pengaduan Penyalahgunaan Wewenang</a></li>
                            <li><a class="dropdown-item" href="{{ route('standard-service.index') }}#sop">SOP PPID</a></li>
                            <li><a class="dropdown-item" href="{{ route('contact.index') }}">Kanal Layanan Informasi</a></li>
                            <li><a class="dropdown-item" href="{{ route('standard-service.index') }}#biaya">Waktu, Biaya & Hari Libur Layanan</a></li>
                            <li><a class="dropdown-item" href="{{ route('standard-service.index') }}#maklumat">Maklumat Pelayanan</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="#" role="button" data-bs-toggle="dropdown">Laporan</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('reports.index') }}#pemda">Laporan Pemkab Empat Lawang</a></li>
                            <li><a class="dropdown-item" href="{{ route('reports.index') }}#ppid">Laporan PPID</a></li>
                            <li><a class="dropdown-item" href="{{ route('reports.index') }}#statistik">Statistik Layanan Informasi</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('news.*') || request()->routeIs('galleries.*') || request()->routeIs('events.*') || request()->routeIs('sppg.*') ? 'active' : '' }}" href="#" role="button" data-bs-toggle="dropdown">Informasi</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('news.index') }}">Berita PPID</a></li>
                            <li><a class="dropdown-item" href="{{ route('galleries.index') }}">Galeri</a></li>
                            <li><a class="dropdown-item" href="{{ route('events.index') }}">Calendar Event</a></li>
                            <li><a class="dropdown-item {{ request()->routeIs('sppg.*') ? 'active' : '' }}" href="{{ route('sppg.index') }}">Makan Bergizi Gratis (SPPG)</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('procurements.*') ? 'active' : '' }}" href="#" role="button" data-bs-toggle="dropdown">Pengadaan</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('procurements.index', ['tab' => 'info']) }}">Informasi Pengadaan Barang & Jasa</a></li>
                            <li><a class="dropdown-item" href="{{ route('procurements.index', ['tab' => 'regulasi']) }}">Regulasi Pengadaan</a></li>
                            <li><a class="dropdown-item" href="http://lpse.empatlawangkab.go.id/" target="_blank">LPSE Empat Lawang</a></li>
                            <li><a class="dropdown-item" href="https://sirup.lkpp.go.id/" target="_blank">SiRUP LKPP</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('contact.*') ? 'active' : '' }}" href="{{ route('contact.index') }}">Kontak</a>
                    </li>

                    <!-- Authentication Links -->
                    @guest
                        @if (Route::has('login'))
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                            </li>
                        @endif
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.dashboard') }}">
                                Dashboard Admin
                            </a>
                        </li>
                    @endguest
                </ul>

                <div class="nav-social d-flex align-items-center gap-1 ms-xl-2 mt-3 mt-xl-0">
                    @forelse(($contactSettings->social_media ?? []) as $social)
                        <a href="{{ $social['url'] ?? '#' }}" target="_blank" rel="noopener" title="{{ $social['name'] ?? '' }}"><i class="{{ social_icon($social) }}"></i></a>
                    @empty
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    @endforelse
                </div>
            </div>
        </div>
    </nav>

    @include('partials.weather')

    <!-- Main Content -->
    <main id="konten-utama" tabindex="-1">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row gy-4">
                <div class="col-lg-4 col-md-6">
                    <div class="d-flex align-items-center mb-3">
                        <img src="{{ asset('assets/images/logo.png') }}" alt="Logo" style="height: 50px; margin-right: 10px; filter: brightness(0) invert(1);">
                        <div>
                            <h5 class="mb-0 text-white border-0 p-0">PPID KABUPATEN</h5>
                            <small class="text-white-50">Empat Lawang</small>
                        </div>
                    </div>
                    <p class="small text-white-50">
                        Pejabat Pengelola Informasi dan Dokumentasi (PPID) Kabupaten Empat Lawang menyediakan akses informasi publik secara transparan, akuntabel, dan efisien sesuai Undang-Undang KIP.
                    </p>
                    <div class="d-flex gap-2 mt-3">
                        @forelse(($contactSettings->social_media ?? []) as $social)
                            <a href="{{ $social['url'] ?? '#' }}" target="_blank" rel="noopener" class="text-white"><i class="{{ social_icon($social) }} fa-lg"></i></a>
                        @empty
                            <a href="#" class="text-white"><i class="fab fa-facebook fa-lg"></i></a>
                            <a href="#" class="text-white"><i class="fab fa-twitter fa-lg"></i></a>
                            <a href="#" class="text-white"><i class="fab fa-instagram fa-lg"></i></a>
                        @endforelse
                    </div>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h5>Tautan Cepat</h5>
                    <ul class="list-unstyled">
                        <li><a href="{{ url('/') }}">Beranda</a></li>
                        <li><a href="{{ route('profiles.show', 'tentang-ppid') }}">Tentang PPID</a></li>
                        <li><a href="{{ route('informasi-publik.index') }}">Informasi Publik</a></li>
                        <li><a href="{{ route('news.index') }}">Berita & Artikel</a></li>
                        <li><a href="{{ route('contact.index') }}">Hubungi Kami</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h5>Layanan Publik</h5>
                    <ul class="list-unstyled">
                        <li><a href="{{ route('request.create') }}">Permohonan Informasi</a></li>
                        <li><a href="{{ route('complaint.create') }}">Pengajuan Keberatan</a></li>
                        <li><a href="{{ route('standard-service.index') }}">Standar Operasional (SOP)</a></li>
                        <li><a href="{{ route('standard-service.index') }}#pengaduan">Pengaduan Penyalahgunaan Wewenang</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h5>Kontak Kami</h5>
                    <ul class="list-unstyled text-white-50 small">
                        <li class="mb-2"><i class="fas fa-map-marker-alt me-2 text-warning"></i> Jl. Lintas Sumatera No. 1, Tebing Tinggi, Empat Lawang, Sumatera Selatan</li>
                        <li class="mb-2"><i class="fas fa-phone me-2 text-warning"></i> (0702) 123456</li>
                        <li class="mb-2"><i class="fas fa-envelope me-2 text-warning"></i> diskominfo@empatlawangkab.go.id</li>
                        <li><i class="fas fa-clock me-2 text-warning"></i> Senin - Jumat: 08:00 - 16:00</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="footer-bottom text-center">
            <div class="container">
                <p class="mb-0">&copy; {{ date('Y') }} PPID Kabupaten Empat Lawang &middot; Empat Lawang Digital Ecosystem - Diskominfo-SP Empat Lawang</p>
                <a href="https://www.bmkg.go.id" target="_blank" rel="noopener" class="bmkg-chip text-decoration-none mt-2" data-bmkg-chip hidden></a>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    @include('partials.accessibility')
    @include('partials.assistant')
    @stack('scripts')
</body>
</html>