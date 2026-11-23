{{-- Verified contact of one agency ($agency); unverified (1 source) contacts are never printed. --}}
<ul class="list-unstyled small mb-0 agency-contact">
    @if ($agency->public_address)
        <li class="mb-1"><i class="fas fa-map-marker-alt text-primary me-2"></i>{{ $agency->public_address }}</li>
    @endif
    @foreach ($agency->public_phones as $phone)
        <li class="mb-1"><i class="fas fa-phone text-primary me-2"></i><a href="tel:{{ $phone['tel'] }}">{{ $phone['number'] }}</a>@if ($phone['label']) <span class="text-muted">({{ $phone['label'] }})</span>@endif</li>
    @endforeach
    @foreach ($agency->public_emails as $email)
        <li class="mb-1 text-break"><i class="fas fa-envelope text-primary me-2"></i><a href="mailto:{{ $email }}">{{ $email }}</a></li>
    @endforeach
    @if ($agency->website)
        <li class="mb-1 text-break"><i class="fas fa-globe text-primary me-2"></i><a href="{{ $agency->website }}" target="_blank" rel="noopener">{{ preg_replace('#^https?://(www\.)?#', '', rtrim($agency->website, '/')) }}</a></li>
    @endif
    @if ($agency->service_hours)
        <li class="mb-1"><i class="fas fa-clock text-primary me-2"></i>{{ $agency->service_hours }}</li>
    @endif
    @if (! $agency->has_public_contact)
        <li class="mb-1 text-muted fst-italic">Kontak belum terverifikasi.</li>
    @endif
</ul>
