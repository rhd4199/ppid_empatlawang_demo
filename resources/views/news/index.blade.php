@extends('layouts.app')

@section('content')
<h2 class="mb-4">Berita Terkini</h2>

<div class="row">
    @forelse($news as $item)
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            @if($item->image)
            <img src="{{ asset('storage/' . $item->image) }}" class="card-img-top" alt="{{ $item->title }}" style="height: 200px; object-fit: cover;">
            @else
            <div class="card-img-top bg-secondary text-white d-flex align-items-center justify-content-center" style="height: 200px;">
                <i class="fas fa-newspaper fa-3x"></i>
            </div>
            @endif
            <div class="card-body">
                <h5 class="card-title">{{ $item->title }}</h5>
                <p class="card-text text-muted small"><i class="far fa-calendar-alt me-1"></i> {{ $item->published_at ? \Carbon\Carbon::parse($item->published_at)->format('d M Y') : '-' }}</p>
                <p class="card-text">{{ Str::limit(strip_tags($item->content), 100) }}</p>
                <a href="{{ route('news.show', $item->slug) }}" class="btn btn-outline-primary stretched-link">Baca Selengkapnya</a>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="alert alert-info">Belum ada berita yang dipublikasikan.</div>
    </div>
    @endforelse
</div>

{{ $news->links() }}
@endsection
