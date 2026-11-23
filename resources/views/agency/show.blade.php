@extends('layouts.app')

@section('title', $agency->name)
@section('description', Str::limit($agency->name . ($agency->public_address ? ' – ' . $agency->public_address : '') . '. ' . $agency->description, 155))

@section('content')
<div class="page-header">
    <div class="container">
        <span class="badge bg-light text-primary mb-2">{{ $agency->category_label }}</span>
        <h1>{{ $agency->name }}</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
                <li class="breadcrumb-item"><a href="{{ route('agencies.index') }}">Perangkat Daerah</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $agency->short_name ?: Str::limit($agency->name, 40) }}</li>
            </ol>
        </nav>
    </div>
</div>

<div class="container pb-5">
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 mb-3">
                <div class="card-body p-4">
                    @if ($agency->short_name)
                        <p class="text-muted mb-2">Singkatan: <strong>{{ $agency->short_name }}</strong></p>
                    @endif
                    @if ($agency->head)
                        <p class="mb-3"><i class="fas fa-user-tie text-primary me-2"></i>Pimpinan: <strong>{{ $agency->head }}</strong>
                            @if ($agency->official_id) <a href="{{ route('officials.index') }}" class="small ms-1">Profil pejabat</a>@endif
                        </p>
                    @endif
                    <h2 class="h5 fw-bold"><i class="fas fa-briefcase text-primary me-2"></i>Tugas & Layanan Utama</h2>
                    <p class="mb-0">{{ $agency->description ?: 'Belum ada uraian tugas.' }}</p>
                </div>
            </div>

            @if ($related->isNotEmpty())
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <h2 class="h6 fw-bold">Lainnya di kategori {{ $agency->category_label }}</h2>
                        <ul class="list-unstyled mb-0 row">
                            @foreach ($related as $other)
                                <li class="py-1 col-md-6"><a href="{{ route('agencies.show', $other->slug) }}">{{ $other->name }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 mb-3">
                <div class="card-body p-4">
                    <h2 class="h6 fw-bold mb-3"><i class="fas fa-address-book text-primary me-2"></i>Kontak</h2>
                    @include('agency._contact')
                    @php($social = array_filter($agency->social ?? []))
                    @if ($social)
                        <div class="d-flex flex-wrap gap-2 mt-3">
                            @foreach ($social as $network => $url)
                                <a href="{{ $url }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary rounded-pill">
                                    <i class="fab fa-{{ $network === 'x' ? 'x-twitter' : $network }} me-1"></i>{{ \App\Models\Agency::SOCIAL[$network] ?? ucfirst($network) }}
                                </a>
                            @endforeach
                        </div>
                    @endif
                    <a href="{{ $agency->maps_url }}" target="_blank" rel="noopener" class="btn btn-primary w-100 mt-3"><i class="fas fa-map-location-dot me-1"></i>Cari di Google Maps</a>
                </div>
            </div>

            <div class="alert alert-warning border-0 shadow-sm small">
                <i class="fas fa-info-circle me-1"></i>
                Kontak dapat berubah sewaktu-waktu. Hanya kontak dari sumber resmi atau dua sumber yang cocok yang ditampilkan.
                @if ($agency->checked_at) Terakhir dicek {{ $agency->checked_at->locale('id')->translatedFormat('j F Y') }}. @endif
                Ada yang keliru? <a href="{{ route('contact.index') }}">Hubungi PPID</a>.
                @php($hosts = collect($agency->sources ?? [])->pluck('url')->filter()->map(fn ($u) => parse_url($u, PHP_URL_HOST))->filter()->unique()->take(5))
                @if ($hosts->isNotEmpty())
                    <div class="mt-1">Sumber: {{ $hosts->implode(', ') }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
