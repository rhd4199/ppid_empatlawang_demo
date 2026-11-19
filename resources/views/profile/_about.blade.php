{{-- Tentang PPID: content split at its headings (content_sections) → icon cards. $about from show.blade.php. --}}
@php
    // Icon per section, matched on its heading; items cycle through the section's icon list.
    $kinds = [
        'latar' => ['fa-book-open', []],
        'pengertian' => ['fa-circle-info', []],
        'prinsip' => ['fa-bolt', ['fa-gauge-high', 'fa-clock', 'fa-coins', 'fa-hand-pointer']],
        'tugas' => ['fa-list-check', ['fa-box-archive', 'fa-folder-open', 'fa-share-nodes', 'fa-headset']],
        'dasar' => ['fa-scale-balanced', ['fa-gavel']],
        'maklumat' => ['fa-handshake', ['fa-star', 'fa-hand-holding-heart', 'fa-circle-check', 'fa-clock', 'fa-list-ul',
            'fa-bullhorn', 'fa-wheelchair', 'fa-scale-balanced', 'fa-ban', 'fa-chart-line']],
    ];
    $kindOf = function ($title) use ($kinds) {
        foreach ($kinds as $key => $kind) {
            if (stripos($title, $key) !== false) return [$key, ...$kind];
        }
        return ['', 'fa-circle-info', ['fa-circle-check']];
    };
@endphp

@if($about['intro'])
    <div class="ab-lead article-content mb-4">{!! $about['intro'] !!}</div>
@endif

@foreach($about['sections'] as $section)
    @php
        [$key, $icon, $itemIcons] = $kindOf($section['title']);
    @endphp
    @if($key === 'latar')
        <details class="ab-more mb-4">
            <summary class="fw-bold"><i class="fas {{ $icon }} me-2"></i>{{ $section['title'] }} <span class="text-muted fw-normal small">— Selengkapnya</span></summary>
            <div class="article-content pt-3">{!! $section['body'] !!}{!! $section['after'] !!}</div>
        </details>
        @continue
    @endif

    <section class="ab-section mb-4">
        <h3 class="ab-title"><span class="ab-icon ab-c{{ $loop->index % 6 }}"><i class="fas {{ $icon }}"></i></span>{{ $section['title'] }}</h3>
        @if($section['body'])
            <div class="article-content mb-3">{!! $section['body'] !!}</div>
        @endif
        @if($section['items'])
            <ul class="ab-cards list-unstyled row g-3 mb-0">
                @foreach($section['items'] as $item)
                    <li class="{{ $key === 'prinsip' ? 'col-6 col-md-3' : 'col-md-6' }}">
                        <div class="ab-card h-100 d-flex gap-3 p-3 rounded-4 ab-c{{ $loop->index % 6 }} {{ $key === 'prinsip' ? 'flex-column align-items-center text-center' : 'align-items-start' }}">
                            <span class="ab-icon flex-shrink-0"><i class="fas {{ $itemIcons[$loop->index % count($itemIcons)] }}"></i></span>
                            <div class="ab-text">{!! $item !!}</div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
        @if($section['after'])
            <div class="article-content mt-3">{!! $section['after'] !!}</div>
        @endif
    </section>
@endforeach

<style>
    .ab-c0 { --ab: #0d6efd; } .ab-c1 { --ab: #198754; } .ab-c2 { --ab: #fd7e14; }
    .ab-c3 { --ab: #6f42c1; } .ab-c4 { --ab: #d63384; } .ab-c5 { --ab: #0dcaf0; }
    .profile-page .ab-lead { font-size: 1.2rem; color: #333; border-left: 4px solid var(--bs-primary); padding-left: 1rem; }
    .ab-title { display: flex; align-items: center; gap: .75rem; font-size: 1.3rem; font-weight: 700; color: #222; margin-bottom: 1rem; }
    .ab-icon { width: 2.5rem; height: 2.5rem; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center;
        background: color-mix(in srgb, var(--ab, #0d6efd) 14%, #fff); color: var(--ab, #0d6efd); font-size: 1.05rem; flex-shrink: 0; }
    .ab-card { background: #fff; border: 1px solid rgba(0,0,0,.07); border-top: 3px solid var(--ab); box-shadow: 0 2px 8px rgba(0,0,0,.04); }
    .ab-text { font-size: .98rem; line-height: 1.5; color: #444; overflow-wrap: anywhere; }
    .ab-text strong { display: block; color: #222; }
    .ab-text p { margin-bottom: 0; }
    .ab-more { background: #f8f9fa; border-radius: 1rem; padding: 1rem 1.25rem; }
    .ab-more summary { cursor: pointer; color: #222; }
</style>
