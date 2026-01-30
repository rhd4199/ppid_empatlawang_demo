@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1>{{ $profile->title }}</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
                        <li class="breadcrumb-item">Profil</li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $profile->title }}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="container pb-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card card-hover p-4 p-md-5">
                <div class="card-body">
                    <div class="article-content" style="font-size: 1.1rem; line-height: 1.8; color: #444;">
                        {!! $profile->content !!}
                    </div>
                </div>
            </div>
            
            <div class="mt-4 text-center">
                <a href="{{ route('home') }}" class="btn btn-outline-primary rounded-pill px-4">
                    <i class="fas fa-arrow-left me-2"></i> Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
