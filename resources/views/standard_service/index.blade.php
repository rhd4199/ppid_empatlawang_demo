@extends('layouts.app')

@section('content')
<h2 class="mb-4">Standar Layanan Publik</h2>

<div class="row">
    <div class="col-md-12">
        <div class="list-group">
            @forelse($documents as $doc)
            <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1">{{ $doc->title }}</h5>
                    <p class="mb-1 text-muted">{{ $doc->description }}</p>
                </div>
                <a href="{{ asset('storage/' . $doc->file_path) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                    <i class="fas fa-download me-1"></i> Unduh
                </a>
            </div>
            @empty
            <div class="alert alert-warning">Belum ada dokumen standar layanan.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
