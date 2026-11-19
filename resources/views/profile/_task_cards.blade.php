{{-- Tugas & Fungsi: each "<h3>Judul</h3><ol>…</ol>" block as a clean numbered two-column list. $ts = content_sections($profile->content), at least one section has a list --}}
@php
    // Drop list punctuation ("…;", "…; dan", "….") — each item stands alone.
    $tidy = fn (string $html) => preg_replace('/\s*(?:;\s*(?:dan|atau)?|\.)\s*((?:<\/[^>]+>\s*)*)$/iu', '$1', $html);
@endphp

@if($ts['intro'])
    <div class="tf-intro article-content mb-4">{!! $ts['intro'] !!}</div>
@endif

@if(count($ts['sections']) > 1)
    <nav class="tf-jump mb-2" aria-label="Lompat ke bagian">
        @foreach($ts['sections'] as $section)
            <a href="#tf-title-{{ $loop->index }}">{{ $section['title'] }}</a>
        @endforeach
    </nav>
@endif

@foreach($ts['sections'] as $section)
    <section class="tf-section" aria-labelledby="tf-title-{{ $loop->index }}">
        <h2 class="tf-title" id="tf-title-{{ $loop->index }}">{{ $section['title'] }}</h2>
        @if($section['body'])
            <div class="article-content tf-body">{!! $section['body'] !!}</div>
        @endif
        @if($section['items'])
            <ol class="tf-list list-unstyled mb-0">
                @foreach($section['items'] as $item)
                    <li class="tf-card">
                        <span class="tf-num" aria-hidden="true">{{ $loop->iteration }}</span>
                        <div class="tf-text">{!! $tidy($item) !!}</div>
                    </li>
                @endforeach
            </ol>
        @endif
        @if($section['after'])
            <div class="article-content mt-3">{!! $section['after'] !!}</div>
        @endif
    </section>
@endforeach

<style>
    .profile-page .tf-intro { font-size: 1.05rem; line-height: 1.7; color: #4b5563; border-left: 4px solid var(--bs-primary); padding-left: 1rem; }
    .tf-jump { display: flex; flex-wrap: wrap; gap: .5rem; }
    .tf-jump a { padding: .4rem .9rem; border-radius: 999px; font-size: .9rem; font-weight: 600; text-decoration: none;
        color: var(--bs-primary); background: rgba(var(--bs-primary-rgb), .08); }
    .tf-jump a:hover { background: rgba(var(--bs-primary-rgb), .15); }
    .tf-section { padding: 2rem 0; border-top: 1px solid #eef0f2; scroll-margin-top: 6rem; }
    .tf-jump + .tf-section { border-top: 0; }
    .tf-title { font-size: 1.2rem; font-weight: 700; color: #1f2937; margin-bottom: 1rem; }
    .tf-title::before { content: ""; display: inline-block; width: 4px; height: 1.1em; border-radius: 2px;
        background: var(--bs-primary); vertical-align: -.15em; margin-right: .6rem; }
    .tf-body { color: #4b5563; margin-bottom: 1rem; }
    .tf-list { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .9rem 2rem; }
    .tf-card { display: flex; gap: .75rem; align-items: baseline; }
    .tf-num { flex-shrink: 0; width: 1.6rem; height: 1.6rem; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center;
        font-size: .8rem; font-weight: 700; color: var(--bs-primary); background: rgba(var(--bs-primary-rgb), .1); }
    .tf-text { color: #374151; line-height: 1.55; }
    .tf-text p { margin-bottom: 0; }
    @media (max-width: 767.98px) { .tf-list { grid-template-columns: minmax(0, 1fr); } }
</style>
