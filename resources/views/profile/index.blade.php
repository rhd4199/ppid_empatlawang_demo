@extends('layouts.app')

@section('title', 'Profil PPID')
@section('description', 'Profil PPID Kabupaten Empat Lawang: tentang PPID, visi dan misi, struktur organisasi, tugas dan fungsi, serta pejabat struktural.')

@php
    $icons = [
        'general' => 'fa-building-columns',
        'visi_misi' => 'fa-bullseye',
        'struktur' => 'fa-sitemap',
        'tugas_fungsi' => 'fa-list-check',
    ];
@endphp

@section('content')
<div class="page-header">
    <div class="container">
        <h1>Profil PPID</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
                <li class="breadcrumb-item active" aria-current="page">Profil</li>
            </ol>
        </nav>
    </div>
</div>

<div class="container pb-5">
    <div class="row g-4">
        @foreach($profiles as $profile)
        <div class="col-sm-6 col-lg-4">
            <a href="{{ route('profiles.show', $profile->slug) }}" class="card profile-card card-hover h-100 border-0 shadow-sm rounded-4 text-decoration-none">
                <div class="card-body p-4">
                    <div class="profile-card-icon mb-3"><i class="fas {{ $icons[$profile->type] ?? 'fa-circle-info' }}" aria-hidden="true"></i></div>
                    <h2 class="h5 fw-bold text-dark mb-2">{{ $profile->title }}</h2>
                    <p class="text-muted small mb-3">{{ Str::limit(plain_text($profile->content), 110) }}</p>
                    <span class="fw-semibold text-primary small">Lihat Detail <i class="fas fa-arrow-right ms-1" aria-hidden="true"></i></span>
                </div>
            </a>
        </div>
        @endforeach
        <div class="col-sm-6 col-lg-4">
            <a href="{{ route('officials.index') }}" class="card profile-card card-hover h-100 border-0 shadow-sm rounded-4 text-decoration-none">
                <div class="card-body p-4">
                    <div class="profile-card-icon mb-3"><i class="fas fa-user-tie" aria-hidden="true"></i></div>
                    <h2 class="h5 fw-bold text-dark mb-2">Pejabat &amp; Pejabat Struktural</h2>
                    <p class="text-muted small mb-3">Daftar pejabat dan pejabat struktural di lingkungan Pemerintah Kabupaten Empat Lawang.</p>
                    <span class="fw-semibold text-primary small">Lihat Detail <i class="fas fa-arrow-right ms-1" aria-hidden="true"></i></span>
                </div>
            </a>
        </div>
    </div>
</div>

<style>
    .profile-card { transition: transform .2s ease, box-shadow .2s ease; }
    .profile-card:hover, .profile-card:focus-visible { transform: translateY(-4px); box-shadow: 0 10px 24px rgba(0,0,0,.1) !important; }
    .profile-card-icon {
        width: 56px; height: 56px; border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        background: rgba(2, 132, 199, .1); color: var(--primary-color); font-size: 1.5rem;
    }
    @media (max-width: 767.98px) {
        .profile-card .card-body { padding: 1.25rem !important; }
    }
</style>
@endsection
