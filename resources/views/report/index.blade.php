@extends('layouts.app')

@section('content')
<h2 class="mb-4">Laporan Penyelenggaraan Pemerintahan & PPID</h2>

<div class="row">
    @forelse($reports as $report)
    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">{{ $report->title }}</h5>
                <h6 class="card-subtitle mb-2 text-muted">{{ ucwords(str_replace('_', ' ', $report->category)) }}</h6>
                <p class="card-text">{{ $report->description }}</p>
                <a href="{{ asset('storage/' . $report->file_path) }}" class="card-link btn btn-primary">Download Laporan</a>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="alert alert-info">Belum ada laporan yang tersedia.</div>
    </div>
    @endforelse
</div>
@endsection
