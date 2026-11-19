{{-- Struktur Organisasi: content table (Unsur | Dijabat oleh | Bertanggung jawab kepada) as a clean vertical hierarchy. $units from show.blade.php. --}}
@if($units['intro'])
    <div class="su-lead article-content mb-4">{!! $units['intro'] !!}</div>
@endif

<ol class="su-list list-unstyled mb-0" aria-label="Susunan {{ $profile->title }}">
    @foreach($units['rows'] as $row)
        @php
            $reportsTo = trim($row[2] ?? '', " \u{2014}-");
        @endphp
        <li class="su-item">
            <div class="su-role">{{ $row[0] }}</div>
            @if(! empty($row[1]))
                <div class="su-holder">{{ $row[1] }}</div>
            @endif
            @if($reportsTo !== '')
                <div class="su-report">{{ $units['head'][2] ?? 'Bertanggung jawab kepada' }}: {{ $reportsTo }}</div>
            @endif
        </li>
    @endforeach
</ol>

@if($units['after'])
    <div class="article-content mt-4">{!! $units['after'] !!}</div>
@endif

<style>
    .profile-page .su-lead { font-size: 1.05rem; line-height: 1.7; color: #4b5563; border-left: 4px solid var(--bs-primary); padding-left: 1rem; }
    .su-list { position: relative; padding-left: 1.75rem; }
    .su-list::before { content: ""; position: absolute; left: .45rem; top: .6rem; bottom: .6rem; width: 2px; background: rgba(var(--bs-primary-rgb), .2); }
    .su-item { position: relative; padding: .9rem 0; border-bottom: 1px solid #eef0f2; }
    .su-item:last-child { border-bottom: 0; }
    .su-item::before { content: ""; position: absolute; left: calc(-1.75rem + .1rem); top: 1.25rem; width: .75rem; height: .75rem;
        border-radius: 50%; background: #fff; border: 2px solid var(--bs-primary); }
    .su-item:first-child::before { background: var(--bs-primary); }
    .su-role { font-weight: 700; color: #1f2937; }
    .su-holder { color: #4b5563; margin-top: .15rem; }
    .su-report { font-size: .85rem; color: #6b7280; margin-top: .25rem; }
</style>
