@extends('layouts.app')

@section('title', $item->title)
@section('description', Str::limit('Persyaratan ' . $item->title . ' di ' . $item->agency . ': ' . plain_text($item->requirements), 155))

@section('content')
<div class="page-header">
    <div class="container">
        <span class="badge bg-light text-primary mb-2">{{ $item->category_label }}</span>
        <h1>{{ $item->title }}</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
                <li class="breadcrumb-item"><a href="{{ route('service-requirements.index') }}">Persyaratan Layanan</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($item->title, 40) }}</li>
            </ol>
        </nav>
    </div>
</div>

<div class="container pb-5">
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <h2 class="h5 fw-bold"><i class="fas fa-list-check text-primary me-2"></i>Persyaratan</h2>
                    <div class="rich-text mb-4">{!! $item->requirements !!}</div>

                    @if ($item->steps)
                        <h2 class="h5 fw-bold"><i class="fas fa-shoe-prints text-primary me-2"></i>Alur / Langkah</h2>
                        <div class="rich-text mb-4">{!! $item->steps !!}</div>
                    @endif

                    @if ($item->legal_basis)
                        <h2 class="h5 fw-bold"><i class="fas fa-scale-balanced text-primary me-2"></i>Dasar Hukum</h2>
                        <div class="rich-text">{!! $item->legal_basis !!}</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 mb-3">
                <div class="card-body p-4">
                    <dl class="mb-0">
                        <dt><i class="fas fa-building text-primary me-1"></i> Instansi</dt>
                        <dd>{{ $item->agency }}</dd>
                        @if ($item->cost)
                            <dt><i class="fas fa-money-bill text-primary me-1"></i> Biaya</dt>
                            <dd>{{ $item->cost }}</dd>
                        @endif
                        @if ($item->duration)
                            <dt><i class="fas fa-clock text-primary me-1"></i> Lama Proses</dt>
                            <dd>{{ $item->duration }}</dd>
                        @endif
                        @if ($item->checked_at)
                            <dt><i class="fas fa-calendar-check text-primary me-1"></i> Terakhir Dicek</dt>
                            <dd>{{ $item->checked_at->locale('id')->translatedFormat('j F Y') }}</dd>
                        @endif
                        @if ($item->source_url)
                            <dt><i class="fas fa-link text-primary me-1"></i> Sumber</dt>
                            <dd class="mb-0 text-break"><a href="{{ $item->source_url }}" target="_blank" rel="noopener nofollow">{{ parse_url($item->source_url, PHP_URL_HOST) }}</a></dd>
                        @endif
                    </dl>
                </div>
            </div>

            <div class="alert alert-warning border-0 shadow-sm small">
                <i class="fas fa-info-circle me-1"></i>
                Informasi umum, bukan keputusan resmi instansi. Persyaratan dan biaya dapat berubah — pastikan kembali ke <strong>{{ $item->agency }}</strong> sebelum datang.
                Ada yang keliru atau perlu ditanyakan? <a href="{{ route('contact.index') }}">Hubungi PPID</a>.
            </div>

            @if ($related->isNotEmpty())
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <h2 class="h6 fw-bold">Layanan {{ $item->category_label }} lainnya</h2>
                        <ul class="list-unstyled mb-0">
                            @foreach ($related as $other)
                                <li class="py-1"><a href="{{ route('service-requirements.show', $other->slug) }}">{{ $other->title }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
