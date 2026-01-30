@extends('layouts.app')

@section('content')
<h2 class="mb-4">Galeri Kegiatan</h2>

<div class="row">
    @forelse($galleries as $gallery)
    <div class="col-md-3 mb-4">
        <div class="card h-100">
            @if($gallery->type == 'photo')
            <img src="{{ asset('storage/' . $gallery->file_path) }}" class="card-img-top" alt="{{ $gallery->title }}" style="height: 150px; object-fit: cover;">
            @else
            <div class="card-img-top bg-dark text-white d-flex align-items-center justify-content-center" style="height: 150px;">
                <i class="fas fa-play-circle fa-3x"></i>
            </div>
            @endif
            <div class="card-body">
                <h6 class="card-title">{{ $gallery->title }}</h6>
                @if($gallery->type == 'video' && $gallery->url)
                <a href="{{ $gallery->url }}" target="_blank" class="btn btn-sm btn-outline-danger mt-2">Tonton Video</a>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="alert alert-info">Belum ada galeri.</div>
    </div>
    @endforelse
</div>

{{ $galleries->links() }}
@endsection
