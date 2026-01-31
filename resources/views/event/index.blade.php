@extends('layouts.app')

@section('title', 'Agenda Kegiatan')

@section('content')
<div class="container py-5">
    <!-- Header Section -->
    <div class="text-center mb-5">
        <h1 class="fw-bold display-5 text-primary">Agenda Kegiatan</h1>
        <p class="text-muted lead">Jadwal kegiatan dan agenda Pemerintah Kabupaten Empat Lawang</p>
    </div>

    <!-- Navigation Tabs -->
    <ul class="nav nav-pills justify-content-center mb-4" id="agendaTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-pill px-4" id="month-tab" data-bs-toggle="tab" data-bs-target="#month-view" type="button" role="tab">
                <i class="fas fa-calendar-alt me-2"></i>Bulan Ini ({{ $currentDate->translatedFormat('F') }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill px-4" id="year-tab" data-bs-toggle="tab" data-bs-target="#year-view" type="button" role="tab">
                <i class="fas fa-list-ul me-2"></i>Tahun Ini ({{ $currentDate->year }})
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="agendaTabsContent">
        
        <!-- MONTH VIEW (Calendar Grid) -->
        <div class="tab-pane fade show active" id="month-view" role="tabpanel">
            <div class="card border-0 shadow-lg overflow-hidden">
                <div class="card-header bg-primary text-white text-center py-3">
                    <h3 class="mb-0 fw-bold">{{ $currentDate->translatedFormat('F Y') }}</h3>
                </div>
                <div class="card-body p-0">
                    <!-- Calendar Header (Days) -->
                    <div class="d-none d-md-grid text-center bg-light border-bottom fw-bold text-secondary py-2" style="grid-template-columns: repeat(7, 1fr);">
                        <div>Senin</div>
                        <div>Selasa</div>
                        <div>Rabu</div>
                        <div>Kamis</div>
                        <div>Jumat</div>
                        <div>Sabtu</div>
                        <div class="text-danger">Minggu</div>
                    </div>

                    <!-- Calendar Grid -->
                    <div class="calendar-grid">
                        @php
                            $day = $startOfCalendar->copy();
                        @endphp

                        <div class="d-grid" style="grid-template-columns: repeat(7, 1fr);">
                            @while($day <= $endOfCalendar)
                                @php
                                    $isToday = $day->isToday();
                                    $isCurrentMonth = $day->month == $currentDate->month;
                                    $dayEvents = $calendarEvents->filter(function($event) use ($day) {
                                        return \Carbon\Carbon::parse($event->start_date)->isSameDay($day);
                                    });
                                @endphp
                                <div class="calendar-day border p-2 {{ !$isCurrentMonth ? 'bg-light text-muted' : 'bg-white' }} {{ $isToday ? 'bg-blue-50' : '' }}" style="min-height: 150px; overflow: hidden;">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="fw-bold {{ $isToday ? 'text-primary rounded-circle bg-white p-1 shadow-sm' : '' }} {{ $day->dayOfWeek == 0 ? 'text-danger' : '' }}" style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">
                                            {{ $day->day }}
                                        </span>
                                        @if($dayEvents->isNotEmpty())
                                            <span class="badge bg-danger rounded-pill">{{ $dayEvents->count() }}</span>
                                        @endif
                                    </div>
                                    
                                    <div class="events-list">
                                        @foreach($dayEvents as $event)
                                            <div class="event-item p-1 mb-1 rounded small bg-primary text-white text-truncate cursor-pointer w-100" 
                                                 data-bs-toggle="modal" 
                                                 data-bs-target="#eventModal{{ $event->id }}"
                                                 title="{{ $event->title }}">
                                                {{ \Carbon\Carbon::parse($event->start_date)->format('H:i') }} {{ $event->title }}
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                @php $day->addDay(); @endphp
                            @endwhile
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- YEAR VIEW (Timeline) -->
        <div class="tab-pane fade" id="year-view" role="tabpanel">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    @forelse($yearEvents as $month => $events)
                        <div class="timeline-month mb-5 position-relative">
                            <h3 class="sticky-top bg-white py-2 border-bottom border-3 border-primary d-inline-block mb-4 text-primary fw-bold">
                                {{ \Carbon\Carbon::parse($events->first()->start_date)->translatedFormat('F') }}
                            </h3>
                            
                            <div class="timeline-items ps-4 border-start border-2">
                                @foreach($events as $event)
                                    <div class="card mb-4 border-0 shadow-sm hover-shadow transition-all">
                                        <div class="card-body p-4">
                                            <div class="row align-items-center">
                                                <div class="col-md-3 text-center border-end mb-3 mb-md-0">
                                                    <h2 class="display-6 fw-bold text-primary mb-0">{{ \Carbon\Carbon::parse($event->start_date)->format('d') }}</h2>
                                                    <span class="text-uppercase small text-muted fw-bold">{{ \Carbon\Carbon::parse($event->start_date)->translatedFormat('l') }}</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <h5 class="card-title fw-bold mb-1">{{ $event->title }}</h5>
                                                    <div class="mb-2 text-muted small">
                                                        <i class="far fa-clock me-1"></i> {{ \Carbon\Carbon::parse($event->start_date)->format('H:i') }} WIB
                                                        <span class="mx-2">|</span>
                                                        <i class="fas fa-map-marker-alt me-1"></i> {{ $event->location }}
                                                    </div>
                                                    <p class="card-text text-secondary mb-0">{{ Str::limit($event->description, 100) }}</p>
                                                    <button class="btn btn-sm btn-link text-decoration-none px-0 mt-2" data-bs-toggle="modal" data-bs-target="#eventModal{{ $event->id }}">
                                                        Lihat Detail <i class="fas fa-arrow-right ms-1"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <img src="https://illustrations.popsy.co/amber/calendar.svg" alt="Empty Calendar" class="mb-4" style="height: 200px;">
                            <h4 class="text-muted">Belum ada agenda kegiatan tahun ini.</h4>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals for All Events -->
@foreach($allEvents as $event)
    <div class="modal fade" id="eventModal{{ $event->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-calendar-check me-2"></i>Detail Kegiatan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <h4 class="fw-bold mb-3">{{ $event->title }}</h4>
                    <div class="d-flex align-items-center text-muted mb-3">
                        <i class="far fa-clock me-2 fa-lg text-primary"></i>
                        <span>
                            {{ \Carbon\Carbon::parse($event->start_date)->translatedFormat('l, d F Y H:i') }}
                            @if($event->end_date)
                                <br>s/d {{ \Carbon\Carbon::parse($event->end_date)->translatedFormat('l, d F Y H:i') }}
                            @endif
                        </span>
                    </div>
                    <div class="d-flex align-items-center text-muted mb-4">
                        <i class="fas fa-map-marker-alt me-2 fa-lg text-danger"></i>
                        <span>{{ $event->location }}</span>
                    </div>
                    <div class="bg-light p-3 rounded">
                        <p class="mb-0">{{ $event->description }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endforeach

<style>
    .bg-blue-50 {
        background-color: #f0f7ff !important;
    }
    .hover-shadow:hover {
        transform: translateY(-3px);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
    }
    .transition-all {
        transition: all 0.3s ease;
    }
    .cursor-pointer {
        cursor: pointer;
    }
    .calendar-day {
        min-height: 150px;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    
    .calendar-grid .row {
        display: flex;
        flex-wrap: wrap;
    }
    
    .calendar-grid .col-md {
        display: flex;
        flex-direction: column;
    }

    .events-list {
        flex: 1;
        overflow-y: auto;
        max-height: 120px; /* Optional: Scroll if too many events */
    }
    
    /* Responsive Calendar Grid for Mobile */
    @media (max-width: 768px) {
        .calendar-day {
            min-height: auto !important;
            height: auto !important;
            border-bottom: 1px solid #dee2e6;
        }
        .col-sm-12 {
            width: 100%;
        }
        .events-list {
            max-height: none;
        }
    }

    /* Custom Active Tab Style */
    .nav-pills .nav-link.active {
        background-color: var(--bs-primary) !important;
        color: #ffffff !important;
        font-weight: bold;
        box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08);
    }
    
    .nav-pills .nav-link {
        color: var(--bs-secondary);
        background-color: #fff;
        border: 1px solid #dee2e6;
        margin: 0 5px;
    }
</style>
@endsection