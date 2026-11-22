@extends('layouts.app')

@section('title', 'Program Makan Bergizi Gratis (MBG) – SPPG di Empat Lawang')
@section('description', 'Daftar SPPG (dapur Makan Bergizi Gratis) di Kabupaten Empat Lawang: jumlah per kecamatan, alamat, status, dan sekolah yang dilayani. Sumber: Badan Gizi Nasional.')

{{-- inline php directives only: a php block here would swallow the inline one in the table (see team notes) --}}
@php($statusClass = ['operasional' => 'success', 'persiapan' => 'warning', 'nonaktif' => 'secondary'])

@section('content')
<div class="page-header">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1>Program Makan Bergizi Gratis (MBG) – SPPG di Empat Lawang</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
                        <li class="breadcrumb-item active" aria-current="page">SPPG MBG</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="container pb-5">
    <p class="text-muted">
        SPPG (Satuan Pelayanan Pemenuhan Gizi) adalah dapur program Makan Bergizi Gratis dari Badan Gizi Nasional (BGN)
        yang memasak dan mengantar makanan ke sekolah, PAUD, pesantren, dan penerima lain di sekitarnya.
    </p>

    <div class="row g-3 mb-4" data-sppg-summary>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm h-100 text-center p-3">
                <div class="display-6 fw-bold text-primary">{{ $total }}</div>
                <div class="small text-muted">SPPG tercatat</div>
            </div>
        </div>
        @foreach (\App\Models\SppgUnit::STATUSES as $key => $label)
            @if ($statusCounts->get($key) || $key === 'operasional')
                <div class="col-6 col-md-3">
                    <div class="card shadow-sm h-100 text-center p-3">
                        <div class="display-6 fw-bold text-{{ $statusClass[$key] }}">{{ $statusCounts->get($key, 0) }}</div>
                        <div class="small text-muted">{{ $label }}</div>
                    </div>
                </div>
            @endif
        @endforeach
        <div class="col-6 col-md-3">
            <div class="card shadow-sm h-100 text-center p-3">
                <div class="display-6 fw-bold text-dark">{{ $servedTotal }}</div>
                <div class="small text-muted">Instansi penerima tercatat</div>
            </div>
        </div>
    </div>

    <div class="card card-hover shadow-sm p-3 mb-4">
        <form action="{{ route('sppg.index') }}" method="GET" role="search">
            @if ($kecamatan)
                <input type="hidden" name="kecamatan" value="{{ $kecamatan }}">
            @endif
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                <input type="search" name="q" class="form-control" value="{{ $q }}" maxlength="100"
                       placeholder="Cari nama SPPG, desa, atau sekolah, mis. SD Negeri 7 Muara Pinang" aria-label="Cari SPPG">
                <button class="btn btn-primary" type="submit">Cari</button>
            </div>
        </form>
        <ul class="nav nav-pills flex-wrap gap-1 mt-3" aria-label="Filter kecamatan">
            <li class="nav-item">
                <a class="nav-link py-1 {{ $kecamatan ? '' : 'active' }}" href="{{ route('sppg.index', array_filter(['q' => $q])) }}">Semua kecamatan <span class="badge bg-light text-dark ms-1">{{ $total }}</span></a>
            </li>
            @foreach ($kecamatans as $name => $count)
                <li class="nav-item">
                    <a class="nav-link py-1 {{ $kecamatan === $name ? 'active' : '' }}" href="{{ route('sppg.index', array_filter(['kecamatan' => $name, 'q' => $q])) }}">{{ $name }} <span class="badge bg-light text-dark ms-1">{{ $count }}</span></a>
                </li>
            @endforeach
        </ul>
    </div>

    @if ($mapped->isNotEmpty())
        <div id="sppgMap" class="rounded shadow-sm mb-4" style="height: 360px;" aria-label="Peta lokasi SPPG"></div>
    @endif

    @if ($q !== '' || $kecamatan)
        <p class="text-muted">{{ $units->count() }} SPPG ditemukan{{ $kecamatan ? ' di Kecamatan ' . $kecamatan : '' }}{!! $q !== '' ? ' untuk "<strong>' . e($q) . '</strong>"' : '' !!}.</p>
    @endif

    @if ($units->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="fas fa-utensils fa-3x opacity-25 mb-3"></i>
            <p class="mb-1">SPPG tidak ditemukan.</p>
            <p class="small">Coba kata lain atau pilih kecamatan lain.</p>
        </div>
    @else
        <div class="table-responsive card shadow-sm">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase">
                    <tr>
                        <th scope="col">Nama SPPG</th>
                        <th scope="col">Kecamatan / Desa</th>
                        <th scope="col">Alamat</th>
                        <th scope="col">Status</th>
                        <th scope="col">Instansi dilayani</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($units as $unit)
                        @php($matched = $q !== '' ? $unit->servedMatching($q) : [])
                        <tr data-sppg-row>
                            <td class="fw-semibold">
                                {{ $unit->name }}
                                @if ($unit->operator)<div class="small text-muted fw-normal">{{ $unit->operator }}</div>@endif
                            </td>
                            <td>{{ $unit->kecamatan }}@if ($unit->village)<div class="small text-muted">{{ $unit->village }}</div>@endif</td>
                            <td class="small" style="min-width: 220px;">{{ $unit->address ?: '-' }}</td>
                            <td><span class="badge bg-{{ $statusClass[$unit->status] ?? 'secondary' }}">{{ $unit->status_label }}</span></td>
                            <td class="small" style="min-width: 200px;">
                                @if ($unit->beneficiaries)<div>{{ number_format($unit->beneficiaries, 0, ',', '.') }} penerima manfaat</div>@endif
                                @if ($unit->institutions_count)
                                    <details {{ $matched ? 'open' : '' }}>
                                        <summary>{{ $unit->institutions_count }} instansi</summary>
                                        <ul class="mb-0 ps-3">
                                            @foreach ($unit->institutions as $inst)
                                                <li class="{{ in_array($inst, $matched, true) ? 'fw-bold' : '' }}">{{ $inst['name'] ?? '' }}@if (! empty($inst['village']))<span class="text-muted"> – {{ $inst['village'] }}</span>@endif</li>
                                            @endforeach
                                        </ul>
                                    </details>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="alert alert-light border small mt-4 mb-0">
        <i class="fas fa-info-circle me-1"></i>
        Sumber data: Radar MBG Badan Gizi Nasional
        @foreach ($sources as $source)
            (<a href="{{ $source }}" target="_blank" rel="noopener">{{ preg_replace('#^https?://(www\.)?#', '', rtrim($source, '/')) }}</a>)
        @endforeach
        @if ($checkedAt), dicek {{ $checkedAt->translatedFormat('j F Y') }}@endif.
        Jumlah instansi adalah sekolah/PAUD/pesantren yang tercatat dilayani tiap SPPG, bukan jumlah penerima manfaat.
        SPPG tahap persiapan belum tentu tercatat. Ada koreksi? Sampaikan melalui <a href="{{ route('contact.index') }}">halaman kontak</a>.
    </div>
</div>
@endsection

@if ($mapped->isNotEmpty())
    @push('scripts')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script>
        (function () {
            var points = @json($mapped->map(fn ($u) => ['name' => $u->name, 'lat' => $u->latitude, 'lng' => $u->longitude]));
            var map = L.map('sppgMap');
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap' }).addTo(map);
            var bounds = points.map(function (p) { L.marker([p.lat, p.lng]).addTo(map).bindPopup(p.name.replace(/</g, '&lt;')); return [p.lat, p.lng]; });
            map.fitBounds(bounds, { maxZoom: 14, padding: [20, 20] });
        })();
    </script>
    @endpush
@endif
