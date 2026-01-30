@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
                <li class="breadcrumb-item"><a href="{{ route('news.index') }}">Berita</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($news->title, 20) }}</li>
            </ol>
        </nav>

        <h1 class="mb-3">{{ $news->title }}</h1>
        <div class="text-muted mb-4">
            <span class="me-3"><i class="far fa-user me-1"></i> {{ $news->author ?? 'Admin' }}</span>
            <span><i class="far fa-calendar-alt me-1"></i> {{ $news->published_at ? \Carbon\Carbon::parse($news->published_at)->format('d F Y') : '-' }}</span>
        </div>

        @if($news->image)
        <img src="{{ asset('storage/' . $news->image) }}" class="img-fluid rounded mb-4 w-100" alt="{{ $news->title }}">
        @endif

        <div class="article-content">
            {!! $news->content !!}
        </div>
    </div>
</div>
@endsection
