{{-- Tentang PPID: content split at its headings (content_sections) → clean, low-icon sections. $about from show.blade.php. --}}
@php
    // Layout per section, matched on its heading; anything else = plain list.
    $kindOf = function ($title) {
        foreach (['latar', 'prinsip', 'dasar', 'maklumat'] as $key) {
            if (stripos($title, $key) !== false) return $key;
        }
        return '';
    };
    // "<strong>Title</strong> description" → [title, description html]; no <strong> → ['', item].
    $split = fn ($item) => preg_match('/^\s*<strong>(.*?)<\/strong>\s*(.*)$/is', $item, $m) ? [$m[1], trim($m[2])] : ['', $item];
@endphp

@if($about['intro'])
    <div class="ab-lead article-content mb-5">{!! $about['intro'] !!}</div>
@endif

@foreach($about['sections'] as $section)
    @php
        $key = $kindOf($section['title']);
    @endphp
    @if($key === 'latar')
        <details class="ab-more">
            <summary>{{ $section['title'] }} <span class="text-muted fw-normal small">— Selengkapnya</span></summary>
            <div class="article-content pt-3">{!! $section['body'] !!}{!! $section['after'] !!}</div>
        </details>
        @continue
    @endif

    <section class="ab-section">
        <h3 class="ab-title">{{ $section['title'] }}</h3>
        @if($section['body'])
            <div class="article-content ab-body">{!! $section['body'] !!}</div>
        @endif

        @if($section['items'] && $key === 'prinsip')
            <ul class="ab-chips list-unstyled mb-0">
                @foreach($section['items'] as $item)
                    <li>{{ trim(html_entity_decode(strip_tags($item))) }}</li>
                @endforeach
            </ul>
        @elseif($section['items'] && $key === 'dasar')
            <dl class="ab-laws mb-0">
                @foreach($section['items'] as $item)
                    @php
                        [$name, $desc] = $split($item);
                    @endphp
                    <div class="ab-law">
                        <dt>{!! $name ?: $desc !!}</dt>
                        @if($name)<dd>{!! $desc !!}</dd>@endif
                    </div>
                @endforeach
            </dl>
        @elseif($section['items'])
            <ul class="ab-checks list-unstyled mb-0">
                @foreach($section['items'] as $item)
                    @php
                        [$name, $desc] = $split($item);
                    @endphp
                    <li>
                        <i class="fas fa-check" aria-hidden="true"></i>
                        <span>@if($name)<strong>{!! $name !!}</strong> @endif{!! $desc !!}</span>
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
    .profile-page .ab-lead { font-size: 1.15rem; line-height: 1.7; color: #333; border-left: 4px solid var(--bs-primary); padding-left: 1rem; }
    .ab-section { padding: 2rem 0; border-top: 1px solid #eef0f2; }
    .ab-title { font-size: 1.2rem; font-weight: 700; color: #1f2937; margin-bottom: 1rem; }
    .ab-title::before { content: ""; display: inline-block; width: 4px; height: 1.1em; border-radius: 2px;
        background: var(--bs-primary); vertical-align: -.15em; margin-right: .6rem; }
    .ab-body { color: #4b5563; margin-bottom: 1rem; }
    .ab-body p:last-child { margin-bottom: 0; }

    .ab-chips { display: flex; flex-wrap: wrap; gap: .5rem; }
    .ab-chips li { padding: .45rem 1rem; border-radius: 999px; font-weight: 600; font-size: .95rem;
        color: var(--bs-primary); background: rgba(var(--bs-primary-rgb), .08); }

    .ab-law { display: grid; grid-template-columns: minmax(0, 15rem) minmax(0, 1fr); gap: .25rem 1.5rem; padding: .75rem 0; border-bottom: 1px dashed #e5e7eb; }
    .ab-law:last-child { border-bottom: 0; }
    .ab-law dt { font-weight: 600; color: #1f2937; }
    .ab-law dd { margin: 0; color: #4b5563; }

    .ab-checks { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .85rem 2rem; }
    .ab-checks li { display: flex; gap: .75rem; align-items: baseline; color: #4b5563; line-height: 1.5; }
    .ab-checks i { color: var(--bs-primary); font-size: .85rem; flex-shrink: 0; }
    .ab-checks strong { color: #1f2937; }

    .ab-more { border-top: 1px solid #eef0f2; padding: 1.5rem 0 0; }
    .ab-more summary { cursor: pointer; font-weight: 700; color: #1f2937; }

    @media (max-width: 767.98px) {
        .ab-law { grid-template-columns: minmax(0, 1fr); }
        .ab-checks { grid-template-columns: minmax(0, 1fr); }
    }
</style>
