{{-- Tugas & Fungsi: each "<h3>Judul</h3><ol>…</ol>" block as a grid of numbered cards with a colored icon. $ts = content_sections($profile->content), at least one section has a list --}}
@php
    // Icon picked from keywords in the item text (first match); otherwise cycles through $fallback.
    $icons = [
        'sengketa' => 'fa-scale-balanced', 'keberatan' => 'fa-scale-balanced', 'menolak' => 'fa-ban',
        'konsekuensi' => 'fa-flask', 'dikecualikan' => 'fa-user-shield', 'verifikasi' => 'fa-check-double',
        'pertanyaan umum' => 'fa-circle-question', 'daftar informasi' => 'fa-list-check', 'laporan' => 'fa-file-lines',
        'sosialisasi' => 'fa-bullhorn', 'menyosialisasikan' => 'fa-bullhorn', 'jawaban' => 'fa-reply',
        'menanggapi' => 'fa-reply', 'pemantauan' => 'fa-magnifying-glass-chart', 'memantau' => 'fa-magnifying-glass-chart',
        'elektronik' => 'fa-laptop', 'menunjuk' => 'fa-user-check', 'menugaskan' => 'fa-user-check',
        'kebijakan' => 'fa-book', 'meminta' => 'fa-hand-holding', 'membantu' => 'fa-handshake',
        'mengoordinasikan' => 'fa-diagram-project', 'memutuskan' => 'fa-gavel', 'menetapkan' => 'fa-stamp',
    ];
    $fallback = ['fa-circle-check', 'fa-lightbulb', 'fa-star', 'fa-flag'];
    $colors = ['#0d6efd', '#198754', '#fd7e14', '#6f42c1', '#d63384', '#20c997', '#dc3545', '#0dcaf0'];
    $iconFor = function (string $html, int $i) use ($icons, $fallback) {
        $text = mb_strtolower(strip_tags($html));
        foreach ($icons as $word => $icon) {
            if (str_contains($text, $word)) {
                return $icon;
            }
        }
        return $fallback[$i % count($fallback)];
    };
    // Drop list punctuation ("…;", "…; dan", "….") — each card stands alone.
    $tidy = fn (string $html) => preg_replace('/\s*(?:;\s*(?:dan|atau)?|\.)\s*((?:<\/[^>]+>\s*)*)$/iu', '$1', $html);
@endphp

@if($ts['intro'])
    <div class="tf-intro article-content mb-4">{!! $ts['intro'] !!}</div>
@endif

@foreach($ts['sections'] as $section)
    <section class="tf-section {{ $loop->last ? '' : 'mb-5' }}" aria-labelledby="tf-title-{{ $loop->index }}">
        <h2 class="tf-title" id="tf-title-{{ $loop->index }}">
            <i class="fas {{ str_contains(mb_strtolower($section['title']), 'wewenang') ? 'fa-gavel' : 'fa-clipboard-list' }} me-2"></i>{{ $section['title'] }}
        </h2>
        @if($section['body'])
            <div class="article-content mb-3">{!! $section['body'] !!}</div>
        @endif
        @if($section['items'])
        <ol class="tf-grid list-unstyled row g-3 mb-0">
            @foreach($section['items'] as $item)
                <li class="col-md-6 col-xl-4">
                    <div class="tf-card h-100 p-3 rounded-4" style="--tf-color: {{ $colors[$loop->index % count($colors)] }}">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="tf-icon"><i class="fas {{ $iconFor($item, $loop->index) }}" aria-hidden="true"></i></span>
                            <span class="tf-num">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        </div>
                        <div class="tf-text">{!! $tidy($item) !!}</div>
                    </div>
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
    .tf-intro { border-left: 4px solid var(--bs-primary); padding: .75rem 1rem; background: #f8f9fa; border-radius: .5rem; }
    .profile-page .tf-intro.article-content { font-size: 1rem; }
    .tf-title { font-size: 1.25rem; font-weight: 700; color: #222; margin-bottom: 1rem; }
    .tf-title i { color: var(--bs-primary); }
    .tf-card { background: #fff; border: 1px solid rgba(0,0,0,.08); border-top: 4px solid var(--tf-color); transition: transform .2s, box-shadow .2s; }
    .tf-card:hover { transform: translateY(-3px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.08); }
    .tf-icon { width: 2.75rem; height: 2.75rem; border-radius: .75rem; display: flex; align-items: center; justify-content: center;
        font-size: 1.2rem; color: var(--tf-color); background: color-mix(in srgb, var(--tf-color) 12%, #fff); }
    .tf-num { font-size: 1.5rem; font-weight: 800; color: var(--tf-color); opacity: .35; line-height: 1; }
    .tf-text { font-size: .95rem; line-height: 1.55; color: #444; }
    .tf-text p { margin-bottom: 0; }
    @media (prefers-reduced-motion: reduce) { .tf-card { transition: none; } .tf-card:hover { transform: none; } }
</style>
