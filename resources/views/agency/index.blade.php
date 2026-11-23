@extends('layouts.app')

@section('title', 'Perangkat Daerah Kabupaten Empat Lawang – Alamat & Kontak OPD')
@section('description', 'Daftar perangkat daerah (OPD) Pemerintah Kabupaten Empat Lawang: sekretariat, dinas, badan, inspektorat, Satpol PP, RSUD, dan 10 kecamatan, lengkap dengan alamat, telepon, email, dan tugas utamanya.')

@section('content')
<div class="page-header">
    <div class="container">
        <h1>Perangkat Daerah Kabupaten Empat Lawang</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
                <li class="breadcrumb-item active" aria-current="page">Perangkat Daerah</li>
            </ol>
        </nav>
    </div>
</div>

<div class="container pb-5">
    <p class="text-muted">
        Alamat, kontak, dan tugas utama perangkat daerah (OPD) Pemerintah Kabupaten Empat Lawang. Hanya kontak dari sumber resmi
        pemerintah atau dua sumber yang cocok yang ditampilkan. Ada data yang keliru? <a href="{{ route('contact.index') }}">Beri tahu PPID</a>.
    </p>

    <div class="card card-hover shadow-sm p-3 mb-4">
        <form action="{{ route('agencies.index') }}" method="GET" role="search">
            @if ($category)
                <input type="hidden" name="kategori" value="{{ $category }}">
            @endif
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                <input type="search" name="q" class="form-control" value="{{ $q }}" maxlength="100"
                       placeholder="Cari instansi, mis. Disdukcapil, kesehatan, Pendopo" aria-label="Cari perangkat daerah">
                <button class="btn btn-primary" type="submit">Cari</button>
            </div>
        </form>
        <ul class="nav nav-pills flex-wrap gap-1 mt-3" aria-label="Filter kategori">
            <li class="nav-item">
                <a class="nav-link py-1 {{ $category ? '' : 'active' }}" href="{{ route('agencies.index', array_filter(['q' => $q])) }}">Semua <span class="badge bg-light text-dark ms-1">{{ $counts->sum() }}</span></a>
            </li>
            @foreach (\App\Models\Agency::CATEGORIES as $key => $label)
                @if ($counts->get($key))
                    <li class="nav-item">
                        <a class="nav-link py-1 {{ $category === $key ? 'active' : '' }}" href="{{ route('agencies.index', array_filter(['kategori' => $key, 'q' => $q])) }}">{{ $label }} <span class="badge bg-light text-dark ms-1">{{ $counts->get($key) }}</span></a>
                    </li>
                @endif
            @endforeach
        </ul>
    </div>

    @if ($q !== '')
        <p class="text-muted">{{ $total }} instansi ditemukan untuk "<strong>{{ $q }}</strong>".</p>
    @endif

    @forelse ($groups as $key => $rows)
        <section class="mb-5" id="{{ $key }}">
            <h2 class="h4 fw-bold mb-3">{{ \App\Models\Agency::CATEGORIES[$key] }}</h2>
            <div class="row g-3">
                @foreach ($rows as $agency)
                    <div class="col-md-6 col-lg-4" data-agency-card>
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-body p-4 d-flex flex-column">
                                <h3 class="h6 fw-bold mb-1"><a href="{{ route('agencies.show', $agency->slug) }}" class="text-decoration-none">{{ $agency->name }}</a></h3>
                                @if ($agency->short_name)<div class="small text-muted mb-1">{{ $agency->short_name }}</div>@endif
                                @if ($agency->head)<div class="small mb-2"><i class="fas fa-user-tie text-primary me-2"></i>{{ $agency->head }}</div>@endif
                                @include('agency._contact')
                                <div class="mt-auto pt-3 d-flex flex-wrap gap-2">
                                    <a href="{{ route('agencies.show', $agency->slug) }}" class="btn btn-sm btn-outline-primary rounded-pill">Profil</a>
                                    <a href="{{ $agency->maps_url }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary rounded-pill"><i class="fas fa-map-location-dot me-1"></i>Google Maps</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @empty
        <div class="text-center py-5 text-muted">
            <i class="fas fa-building fa-3x opacity-25 mb-3"></i>
            <p class="mb-1">Instansi tidak ditemukan.</p>
            <p class="small">Coba kata lain atau pilih kategori lain.</p>
        </div>
    @endforelse
</div>
@endsection
