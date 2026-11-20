{{-- Kalender agenda bulan berjalan. Data: $calendarMonth (awal bulan), $monthEvents (Event per tanggal) dari HomeController. --}}
@php
    $calMonth = $calendarMonth->copy()->locale('id');
    $leadingBlanks = $calMonth->dayOfWeekIso - 1; // grid mulai Senin
    $today = now()->isSameMonth($calMonth) ? now()->day : null;
@endphp
<style>
    .home-cal { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); gap: 6px; }
    .home-cal-head { text-align: center; font-size: .8rem; font-weight: 700; color: var(--text-light); padding: 4px 0; }
    .home-cal-day { min-height: 92px; border-radius: 10px; background: var(--bg-light); border: 1px solid #e9ecef; padding: 6px; font-size: .8rem; color: var(--text-dark); text-decoration: none; display: flex; flex-direction: column; gap: 3px; overflow: hidden; }
    .home-cal-day.is-blank { background: transparent; border: 0; }
    .home-cal-day.is-today { border-color: var(--primary-color); box-shadow: inset 0 0 0 1px var(--primary-color); }
    .home-cal-day.has-event { background: #e0f2fe; border-color: #7dd3fc; }
    .home-cal-day.has-event:hover { background: #bae6fd; }
    .home-cal-num { font-weight: 700; }
    .home-cal-event { display: block; line-height: 1.2; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: var(--accent-color); }
    .home-cal-dot { display: none; width: 6px; height: 6px; border-radius: 50%; background: var(--primary-color); margin: 0 auto; }
    @media (max-width: 767.98px) {
        .home-cal { gap: 3px; }
        .home-cal-day { min-height: 44px; padding: 4px 2px; align-items: center; }
        .home-cal-event { display: none; }
        .home-cal-dot { display: block; }
    }
</style>

<section class="py-5">
    <div class="container">
        <div class="section-title text-center mb-4">
            <a href="{{ route('events.index') }}" style="text-decoration: none"><h2>Agenda {{ $calMonth->translatedFormat('F Y') }}</h2></a>
            <p>Kalender kegiatan Pemerintah Kabupaten Empat Lawang bulan ini</p>
        </div>

        <div class="home-cal" data-days="{{ $calMonth->daysInMonth }}">
            @foreach (['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $dayName)
                <div class="home-cal-head">{{ $dayName }}</div>
            @endforeach

            @for ($i = 0; $i < $leadingBlanks; $i++)
                <div class="home-cal-day is-blank" aria-hidden="true"></div>
            @endfor

            @for ($day = 1; $day <= $calMonth->daysInMonth; $day++)
                @php $dayEvents = $monthEvents->get($day); @endphp
                @if ($dayEvents)
                    <a href="{{ route('events.index') }}" class="home-cal-day has-event {{ $day === $today ? 'is-today' : '' }}"
                       title="{{ $dayEvents->map(fn ($e) => \Carbon\Carbon::parse($e->start_date)->format('H:i') . ' ' . $e->title)->implode(' · ') }}">
                        <span class="home-cal-num">{{ $day }}</span>
                        @foreach ($dayEvents->take(2) as $event)
                            <span class="home-cal-event">{{ \Carbon\Carbon::parse($event->start_date)->format('H:i') }} {{ $event->title }}</span>
                        @endforeach
                        @if ($dayEvents->count() > 2)
                            <span class="home-cal-event small">+{{ $dayEvents->count() - 2 }} lainnya</span>
                        @endif
                        <span class="home-cal-dot"></span>
                    </a>
                @else
                    <div class="home-cal-day {{ $day === $today ? 'is-today' : '' }}"><span class="home-cal-num">{{ $day }}</span></div>
                @endif
            @endfor
        </div>

        {{-- HP: judul tak muat di sel → daftar agenda di bawah kalender --}}
        @if ($monthEvents->isNotEmpty())
            <ul class="list-unstyled mt-3 mb-0 d-md-none">
                @foreach ($monthEvents->flatten() as $event)
                    @php $start = \Carbon\Carbon::parse($event->start_date)->locale('id'); @endphp
                    <li class="mb-2">
                        <a href="{{ route('events.index') }}" class="d-flex gap-2 text-decoration-none text-dark">
                            <span class="fw-bold text-primary text-nowrap">{{ $start->translatedFormat('d M') }}</span>
                            <span>{{ $start->format('H:i') }} · {{ $event->title }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-center text-muted mt-3 mb-0">Belum ada agenda bulan ini.</p>
        @endif

        <div class="text-center mt-4">
            <a href="{{ route('events.index') }}" class="btn btn-outline-primary rounded-pill px-4">Lihat Semua Agenda</a>
        </div>
    </div>
</section>
