<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Admin PPID') }} - Admin Panel</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    @stack('styles')
    
    <style>
        :root {
            --primary-color: #0284c7;
            --secondary-color: #ffc107;
            --accent-color: #0369a1;
            
            /* Bootstrap Variables */
            --bs-primary: #0284c7;
            --bs-primary-rgb: 2, 132, 199;
            --bs-secondary: #ffc107;
            --bs-secondary-rgb: 255, 193, 7;

            --sidebar-width: 280px;
            --header-height: 70px;
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
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
            overflow-x: hidden;
        }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background: linear-gradient(180deg, var(--primary-color) 0%, #00251a 100%);
            color: white;
            z-index: 1000;
            transition: all 0.3s;
            overflow-y: auto;
        }

        .sidebar { z-index: 1200 !important; }
        .top-header { z-index: 1100 !important; }

        /* FullCalendar jangan boleh punya z-index yang menang */
        .fc, .fc * { z-index: auto; }

        .sidebar-brand {
            height: var(--header-height);
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            font-weight: 700;
            font-size: 1.25rem;
            color: white;
            text-decoration: none;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-menu {
            padding: 1rem 0;
        }

        .menu-header {
            padding: 0.75rem 1.5rem 0.25rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: rgba(255,255,255,0.4);
            font-weight: 600;
        }

        .menu-item {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.2s;
            border-left: 4px solid transparent;
        }

        .menu-item:hover, .menu-item.active {
            background-color: rgba(255,255,255,0.05);
            color: white;
            border-left-color: var(--secondary-color);
        }

        .menu-item i {
            width: 24px;
            margin-right: 0.75rem;
            text-align: center;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: all 0.3s;
        }

        /* Header */
        .top-header {
            height: var(--header-height);
            background-color: white;
            box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            position: sticky;
            top: 0;
            z-index: 900;
        }

        .header-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.25rem;
            color: var(--text-dark);
        }

        .user-dropdown .dropdown-toggle {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: #333;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            background-color: var(--primary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-right: 0.75rem;
        }

        /* Responsive */
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
                width: 100%;
            }
            .header-toggle {
                display: block;
            }
            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0,0,0,0.5);
                z-index: 999;
            }
            .sidebar-overlay.show {
                display: block;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
            <img src="{{ asset('assets/images/logo.png') }}" alt="PPID Empat Lawang" style="max-height: 40px; filter: brightness(0) invert(1);">
        </a>
        
        <div class="sidebar-menu">
            <a href="{{ route('admin.dashboard') }}" class="menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>

            <div class="menu-header">Konten Website</div>
            
            <!-- Profil Dropdown -->
            <a href="#profileSubmenu" data-bs-toggle="collapse" class="menu-item dropdown-toggle {{ request()->routeIs('admin.profiles.*') ? 'active' : '' }}" role="button" aria-expanded="{{ request()->routeIs('admin.profiles.*') ? 'true' : 'false' }}">
                <i class="fas fa-user-tie"></i> Profil
            </a>
            <div class="collapse {{ request()->routeIs('admin.profiles.*') ? 'show' : '' }}" id="profileSubmenu">
                <div class="bg-black bg-opacity-25 py-2">
                    <a href="{{ route('admin.profiles.edit', 'tentang-ppid') }}" class="menu-item ps-5 py-2 small {{ request()->is('admin/profil/tentang-ppid/edit') ? 'text-warning' : '' }}">
                        <i class="fas fa-angle-right fa-xs me-2"></i> Tentang PPID
                    </a>
                    <a href="{{ route('admin.profiles.edit', 'visi-misi') }}" class="menu-item ps-5 py-2 small {{ request()->is('admin/profil/visi-misi/edit') ? 'text-warning' : '' }}">
                        <i class="fas fa-angle-right fa-xs me-2"></i> Visi Misi
                    </a>
                    <a href="{{ route('admin.profiles.edit', 'struktur-organisasi') }}" class="menu-item ps-5 py-2 small {{ request()->is('admin/profil/struktur-organisasi/edit') ? 'text-warning' : '' }}">
                        <i class="fas fa-angle-right fa-xs me-2"></i> Struktur Organisasi
                    </a>
                    <a href="{{ route('admin.profiles.edit', 'tugas-fungsi') }}" class="menu-item ps-5 py-2 small {{ request()->is('admin/profil/tugas-fungsi/edit') ? 'text-warning' : '' }}">
                        <i class="fas fa-angle-right fa-xs me-2"></i> Tugas & Fungsi
                    </a>
                </div>
            </div>
            
            <a href="{{ route('admin.info-public.index') }}" class="menu-item {{ request()->routeIs('admin.info-public.*') ? 'active' : '' }}">
                <i class="fas fa-file-alt"></i> Informasi Publik
            </a>
            
            <a href="{{ route('admin.standard-service.index') }}" class="menu-item {{ request()->routeIs('admin.standard-service.*') ? 'active' : '' }}">
                <i class="fas fa-hand-holding-heart"></i> Standar Layanan
            </a>
            
            <a href="{{ route('admin.reports.index') }}" class="menu-item {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                <i class="fas fa-chart-bar"></i> Laporan
            </a>

            <!-- Info Dropdown -->
            <a href="#infoSubmenu" data-bs-toggle="collapse" class="menu-item dropdown-toggle {{ request()->routeIs('admin.news.*') || request()->routeIs('admin.galleries.*') || request()->routeIs('admin.events.*') ? 'active' : '' }}" role="button" aria-expanded="false">
                <i class="fas fa-info-circle"></i> Informasi
            </a>
            <div class="collapse {{ request()->routeIs('admin.news.*') || request()->routeIs('admin.galleries.*') || request()->routeIs('admin.events.*') ? 'show' : '' }}" id="infoSubmenu">
                <div class="bg-black bg-opacity-25 py-2">
                    <a href="{{ route('admin.news.index') }}" class="menu-item ps-5 py-2 small {{ request()->routeIs('admin.news.*') ? 'text-warning' : '' }}">
                        <i class="fas fa-newspaper me-2"></i> Berita PPID
                    </a>
                    <a href="{{ route('admin.galleries.index') }}" class="menu-item ps-5 py-2 small {{ request()->routeIs('admin.galleries.*') ? 'text-warning' : '' }}">
                        <i class="fas fa-images me-2"></i> Galeri
                    </a>
                    <a href="{{ route('admin.events.index') }}" class="menu-item ps-5 py-2 small {{ request()->routeIs('admin.events.*') ? 'text-warning' : '' }}">
                        <i class="fas fa-calendar-alt me-2"></i> Agenda
                    </a>
                </div>
            </div>
            
            <a href="{{ route('admin.procurements.index') }}" class="menu-item {{ request()->routeIs('admin.procurements.*') ? 'active' : '' }}">
                <i class="fas fa-shopping-cart"></i> Pengadaan
            </a>
            
            <div class="menu-header">Interaksi</div>
            
            <!-- Interaksi Dropdown -->
            <a href="#interactionSubmenu" data-bs-toggle="collapse" class="menu-item dropdown-toggle {{ request()->routeIs('admin.contact.*') || request()->routeIs('admin.requests.*') || request()->routeIs('admin.complaints.*') ? 'active' : '' }}" role="button" aria-expanded="false">
                <i class="fas fa-comments"></i> Interaksi
            </a>
            <div class="collapse {{ request()->routeIs('admin.contact.*') || request()->routeIs('admin.requests.*') || request()->routeIs('admin.complaints.*') ? 'show' : '' }}" id="interactionSubmenu">
                <div class="bg-black bg-opacity-25 py-2">
                    <a href="{{ route('admin.contact.index') }}" class="menu-item ps-5 py-2 small {{ request()->routeIs('admin.contact.*') ? 'text-warning' : '' }}">
                        <i class="fas fa-envelope me-2"></i> Pesan Masuk
                    </a>
                    <a href="{{ route('admin.requests.index') }}" class="menu-item ps-5 py-2 small {{ request()->routeIs('admin.requests.*') ? 'text-warning' : '' }}">
                        <i class="fas fa-clipboard-list me-2"></i> Permohonan Info
                    </a>
                    <a href="{{ route('admin.complaints.index') }}" class="menu-item ps-5 py-2 small {{ request()->routeIs('admin.complaints.*') ? 'text-warning' : '' }}">
                        <i class="fas fa-exclamation-circle me-2"></i> Pengajuan Keberatan
                    </a>
                </div>
            </div>
            
            <div class="menu-header">Pengaturan</div>

            <a href="{{ route('admin.contact-settings.index') }}" class="menu-item {{ request()->routeIs('admin.contact-settings.*') ? 'active' : '' }}">
                <i class="fas fa-cogs"></i> Kontak & Alamat
            </a>

            <div class="menu-header">Website</div>
            <a href="{{ url('/') }}" class="menu-item">
                <i class="fas fa-home"></i> Kembali ke Website
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Header -->
        <header class="top-header">
            <button class="header-toggle" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            
            <h5 class="mb-0 d-none d-md-block fw-bold text-dark"></h5>

            <div class="dropdown user-dropdown">
                <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown">
                    <div class="user-avatar">
                        {{ substr(Auth::user()->name ?? 'A', 0, 1) }}
                    </div>
                    <span class="d-none d-md-block fw-medium">{{ Auth::user()->name ?? 'Admin' }}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2">
                    <li><h6 class="dropdown-header">Akun Saya</h6></li>
                    <li><a class="dropdown-item" href="{{ route('admin.account.index') }}"><i class="fas fa-user-cog me-2 text-muted"></i> Pengaturan</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item text-danger" href="{{ route('logout') }}"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </li>
                </ul>
            </div>
        </header>

        <!-- Page Content -->
        <main class="p-4" style="padding-top: calc(var(--header-height) + 1rem);">
            @yield('content')
        </main>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('show');
            document.getElementById('sidebarOverlay').classList.toggle('show');
        });

        document.getElementById('sidebarOverlay').addEventListener('click', function() {
            document.getElementById('sidebar').classList.remove('show');
            this.classList.remove('show');
        });
    </script>
    @stack('modals')
    @stack('scripts')
</body>
</html>
