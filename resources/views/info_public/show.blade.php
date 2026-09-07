@extends('layouts.app')

@section('title', $info->title)

@php
    $categoryLabels = [
        'informasi-publik-berkala' => 'Informasi Berkala',
        'informasi-publik-serta-merta' => 'Informasi Serta Merta',
        'informasi-publik-setiap-saat' => 'Informasi Setiap Saat',
        'informasi-publik-dikecualikan' => 'Informasi Dikecualikan',
    ];
@endphp

@section('content')
<div class="page-header position-relative overflow-hidden">
    <div class="position-absolute top-0 start-0 w-100 h-100 overflow-hidden" style="z-index: 0;">
        <div class="shape shape-1" style="opacity: 0.1;"></div>
        <div class="shape shape-2" style="opacity: 0.1;"></div>
    </div>
    <div class="container position-relative" style="z-index: 1;">
        <div class="row justify-content-center">
            <div class="col-md-10 text-center">
                <span class="badge bg-light text-primary mb-2">{{ $categoryLabels[$info->category] ?? 'Informasi Publik' }}</span>
                <h1 class="display-5 fw-bold mb-3">{{ $info->title }}</h1>
                <nav aria-label="breadcrumb" class="d-flex justify-content-center">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-white text-opacity-75 text-decoration-none">Beranda</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('informasi-publik.index') }}" class="text-white text-opacity-75 text-decoration-none">Informasi Publik</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">{{ Str::limit($info->title, 40) }}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="container pb-5 mt-n5 position-relative" style="z-index: 2;">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-body p-4 p-md-5 bg-white">

                    @if ($info->external_url)
                        <div class="alert alert-primary border-0 shadow-sm d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <span><i class="fas fa-arrow-up-right-from-square me-2"></i>Dokumen/tautan lengkap tersedia di link berikut.</span>
                            <a href="{{ $info->external_url }}" target="_blank" rel="noopener" class="btn btn-primary rounded-pill px-4">
                                Buka Dokumen <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    @endif

                    @if ($info->description)
                        <div class="article-content" style="font-size: 1.05rem; line-height: 1.8; color: #444;">
                            {!! $info->description !!}
                        </div>
                    @endif

                    @if ($info->file_path)
                        <div class="mt-4 pt-4 border-top">
                            <a href="{{ storage_url($info->file_path) }}" target="_blank" class="btn btn-success rounded-pill px-4">
                                <i class="fas fa-download me-2"></i>Unduh Dokumen
                            </a>
                        </div>
                    @endif

                    @if (! $info->external_url && ! $info->description && ! $info->file_path)
                        <p class="text-muted mb-0">Konten untuk informasi ini belum tersedia.</p>
                    @endif
                </div>
            </div>

            <div class="mt-5 text-center">
                <a href="{{ route('informasi-publik.index') }}" class="btn btn-outline-primary rounded-pill px-4 py-2 fw-bold">
                    <i class="fas fa-arrow-left me-2"></i> Kembali ke Daftar Informasi Publik
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    .mt-n5 { margin-top: -3rem; }
    .shape { position: absolute; border-radius: 50%; filter: blur(80px); }
    .shape-1 { background: #ffffff; width: 400px; height: 400px; top: -100px; right: -100px; }
    .shape-2 { background: #ffffff; width: 300px; height: 300px; bottom: -50px; left: -100px; }
</style>
@endsection
