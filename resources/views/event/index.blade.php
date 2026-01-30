@extends('layouts.app')

@section('content')
<h2 class="mb-4">Agenda Kegiatan</h2>

<div class="list-group">
    @forelse($events as $event)
    <div class="list-group-item list-group-item-action flex-column align-items-start">
        <div class="d-flex w-100 justify-content-between">
            <h5 class="mb-1">{{ $event->title }}</h5>
            <small class="text-muted">
                {{ \Carbon\Carbon::parse($event->start_date)->format('d M Y H:i') }} 
                @if($event->end_date)
                 - {{ \Carbon\Carbon::parse($event->end_date)->format('d M Y H:i') }}
                @endif
            </small>
        </div>
        <p class="mb-1">{{ $event->description }}</p>
        <small class="text-primary"><i class="fas fa-map-marker-alt me-1"></i> {{ $event->location }}</small>
    </div>
    @empty
    <div class="alert alert-info">Belum ada agenda kegiatan mendatang.</div>
    @endforelse
</div>

<div class="mt-3">
    {{ $events->links() }}
</div>
@endsection
