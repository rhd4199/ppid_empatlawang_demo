{{-- Kalender agenda bulan berjalan. Data: $calendarMonth (awal bulan), $monthEvents (Event per tanggal) dari HomeController. --}}
@php
    $calMonth = $calendarMonth->copy()->locale('id');
    $leadingBlanks = $calMonth->dayOfWeekIso - 1; // grid mulai Senin
    $today = now()->isSameMonth($calMonth) ? now()->day : null;
    $todayStart = now()->startOfDay();
@endphp
<style>
    .home-agenda { display: grid; grid-template-columns: 300px minmax(0, 1fr); gap: 40px; align-items: start; max-width: 880px; margin: 0 auto; }
    .home-cal-month { font-size: .95rem; font-weight: 600; color: var(--primary-color); margin-bottom: 10px; letter-spacing: .01em; }
    .home-cal { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); row-gap: 2px; }
    .home-cal-head { text-align: center; font-size: .7rem; font-weight: 500; color: var(--text-light); text-transform: uppercase; padding-bottom: 6px; }
    .home-cal-day { position: relative; display: flex; align-items: center; justify-content: center; height: 38px; color: var(--text-dark); text-decoration: none; }
    .home-cal-num { display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 50%; font-size: .875rem; font-weight: 400; transition: background-color .15s; }
    .home-cal-day.has-event .home-cal-num { color: var(--primary-color); font-weight: 600; }
    .home-cal-day.has-event::after { content: ""; position: absolute; bottom: 1px; width: 4px; height: 4px; border-radius: 50%; background: var(--primary-color); }
    .home-cal-day.has-event:hover .home-cal-num { background: rgba(2, 132, 199, .1); }
    .home-cal-day.is-today .home-cal-num { background: var(--primary-color); color: #fff; font-weight: 600; }
    .home-agenda-list { list-style: none; margin: 0; padding: 0; max-height: 300px; overflow-y: auto; }
    .home-agenda-item a { display: flex; gap: 16px; align-items: center; padding: 10px 4px; border-bottom: 1px solid rgba(0, 0, 0, .06); color: var(--text-dark); text-decoration: none; }
    .home-agenda-item:last-child a { border-bottom: 0; }
    .home-agenda-item a:hover .home-agenda-title { color: var(--primary-color); }
    .home-agenda-item.is-past { opacity: .5; }
    .home-agenda-date { flex: 0 0 44px; text-align: center; line-height: 1.1; }
    .home-agenda-date b { display: block; font-size: 1.5rem; font-weight: 300; color: var(--primary-color); }
    .home-agenda-date small { font-size: .7rem; text-transform: uppercase; color: var(--text-light); }
    .home-agenda-meta { font-size: .75rem; color: var(--text-light); }
    .home-agenda-title { font-size: .95rem; font-weight: 500; line-height: 1.3; overflow-wrap: anywhere; }
    .home-agenda-empty { color: var(--text-light); font-weight: 300; padding: 24px 0; }
    @media (max-width: 767.98px) {
        .home-agenda { grid-template-columns: minmax(0, 1fr); gap: 20px; max-width: 360px; }
        .home-agenda-list { max-height: none; }
    }
</style>

<section class="py-5">
    <div class="container">
        <div class="section-title text-center mb-4">
            <a href="{{ route('events.index') }}" style="text-decoration: none"><h2>Agenda {{ $calMonth->translatedFormat('F Y') }}</h2></a>
            <p>Kalender kegiatan Pemerintah Kabupaten Empat Lawang bulan ini</p>
        </div>

        <div class="home-agenda">
            <div>
                <div class="home-cal-month">{{ $calMonth->translatedFormat('F') }}</div>
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
                            </a>
                        @else
                            <div class="home-cal-day {{ $day === $today ? 'is-today' : '' }}"><span class="home-cal-num">{{ $day }}</span></div>
                        @endif
                    @endfor
                </div>
            </div>

            <div>
                @if ($monthEvents->isNotEmpty())
                    <ul class="home-agenda-list">
                        @foreach ($monthEvents->flatten() as $event)
                            @php $start = \Carbon\Carbon::parse($event->start_date)->locale('id'); @endphp
                            <li class="home-agenda-item {{ $start->lt($todayStart) ? 'is-past' : '' }}">
                                <a href="{{ route('events.index') }}">
                                    <span class="home-agenda-date"><b>{{ $start->day }}</b><small>{{ $start->translatedFormat('D') }}</small></span>
                                    <span>
                                        <span class="home-agenda-meta">{{ $start->format('H:i') }}@if ($event->location) · {{ $event->location }}@endif</span>
                                        <span class="home-agenda-title d-block">{{ $event->title }}</span>
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="home-agenda-empty text-center mb-0">Belum ada agenda bulan ini.</p>
                @endif
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="{{ route('events.index') }}" class="btn btn-outline-primary rounded-pill px-4">Lihat Semua Agenda</a>
        </div>
    </div>
</section>
