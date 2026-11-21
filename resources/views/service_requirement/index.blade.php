@extends('layouts.app')

@section('title', 'Persyaratan Layanan')
@section('description', 'Persyaratan, langkah, biaya dan lama proses layanan publik di Kabupaten Empat Lawang: KTP-el, KK, akta, SIM, STNK, sertifikat tanah, izin usaha, mutasi ASN.')

@section('content')
<div class="page-header">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1>Persyaratan Layanan</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Persyaratan Layanan</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="container pb-5">
    <div class="card card-hover shadow-sm p-3 mb-4">
        <form action="{{ route('service-requirements.index') }}" method="GET" role="search">
            @if ($category)
                <input type="hidden" name="kategori" value="{{ $category }}">
            @endif
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                <input type="search" name="q" class="form-control" value="{{ $q }}" maxlength="100"
                       placeholder="Cari layanan, mis. ktp hilang, balik nama, pirt..." aria-label="Cari persyaratan layanan">
                <button class="btn btn-primary" type="submit">Cari</button>
            </div>
        </form>
        <ul class="nav nav-pills flex-wrap gap-1 mt-3">
            <li class="nav-item">
                <a class="nav-link py-1 {{ $category ? '' : 'active' }}" href="{{ route('service-requirements.index', array_filter(['q' => $q])) }}">Semua</a>
            </li>
            @foreach ($categories as $key => $label)
                <li class="nav-item">
                    <a class="nav-link py-1 {{ $category === $key ? 'active' : '' }}" href="{{ route('service-requirements.index', array_filter(['kategori' => $key, 'q' => $q])) }}">{{ $label }}</a>
                </li>
            @endforeach
        </ul>
    </div>

    <div class="alert alert-warning border-0 shadow-sm small">
        <i class="fas fa-info-circle me-1"></i>
        Informasi umum yang dihimpun PPID dari peraturan dan sumber resmi. Persyaratan dapat berubah — pastikan kembali ke instansi yang melayani sebelum datang.
    </div>

    @if ($q !== '')
        <p class="text-muted">{{ $total }} layanan ditemukan untuk "<strong>{{ $q }}</strong>".</p>
    @endif

    @forelse ($groups as $key => $group)
        <section class="mb-4" id="{{ $key }}">
            <h2 class="h5 fw-bold mb-3">{{ $group['label'] }}</h2>
            <div class="row g-3">
                @foreach ($group['items'] as $item)
                    <div class="col-md-6 col-lg-4">
                        <a href="{{ route('service-requirements.show', $item->slug) }}" class="card card-hover h-100 shadow-sm text-decoration-none">
                            <div class="card-body">
                                <h3 class="h6 fw-bold text-dark mb-2">{{ $item->title }}</h3>
                                <div class="small text-muted"><i class="fas fa-building me-1"></i>{{ $item->agency }}</div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </section>
    @empty
        <div class="text-center py-5 text-muted">
            <i class="fas fa-search fa-3x opacity-25 mb-3"></i>
            <p class="mb-1">Layanan tidak ditemukan.</p>
            <p class="small">Coba kata lain, atau tanyakan ke PPID melalui <a href="{{ route('contact.index') }}">halaman kontak</a>.</p>
        </div>
    @endforelse
</div>
@endsection
