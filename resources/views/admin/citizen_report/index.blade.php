@extends('layouts.admin')

@section('title', 'Laporan Warga')

@section('content')
<div class="card border-0 shadow-lg rounded-4 overflow-hidden">
    <div class="card-header bg-gradient bg-primary text-white py-4">
        <h5 class="mb-1 fw-bold"><i class="fas fa-bullhorn me-2"></i>Laporan Warga</h5>
        <small class="opacity-75">Aduan yang dicatat Sapa Madani dari WhatsApp dan chat situs. Teruskan ke instansi terkait, lalu ubah statusnya.</small>
    </div>
    <div class="card-body p-0">
        @if (session('success'))
            <div class="alert alert-success m-3 mb-0 border-0">{{ session('success') }}</div>
        @endif
        <form method="GET" class="d-flex flex-wrap gap-2 p-3 border-bottom">
            <input type="search" name="q" value="{{ $q }}" class="form-control form-control-sm" style="max-width: 260px;" placeholder="Cari kode, uraian, lokasi...">
            <select name="status" class="form-select form-select-sm" style="max-width: 230px;" onchange="this.form.submit()">
                <option value="">Semua status ({{ $counts->sum() }})</option>
                @foreach (App\Models\CitizenReport::STATUSES as $key => $label)
                    <option value="{{ $key }}" @selected($status === $key)>{{ $label }} ({{ $counts[$key] ?? 0 }})</option>
                @endforeach
            </select>
        </form>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light small text-uppercase text-secondary">
                    <tr><th class="px-3">Kode / waktu</th><th>Laporan</th><th>Pelapor</th><th style="width: 320px;">Tindak lanjut</th></tr>
                </thead>
                <tbody>
                    @forelse ($reports as $report)
                        <tr>
                            <td class="px-3 small">
                                <div class="fw-bold">{{ $report->code }}</div>
                                <div class="text-muted">{{ $report->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}</div>
                                <span class="badge bg-light text-secondary border">{{ ucfirst($report->channel) }}</span>
                            </td>
                            <td class="small">
                                <div class="fw-semibold">{{ App\Models\CitizenReport::CATEGORIES[$report->category] ?? $report->category }}</div>
                                <div>{{ $report->description }}</div>
                                <div class="text-muted"><i class="fas fa-location-dot me-1"></i>{{ $report->location }}{{ $report->village ? ', Desa ' . $report->village : '' }}{{ $report->kecamatan ? ', Kec. ' . $report->kecamatan : '' }}</div>
                                @if ($report->occurred)<div class="text-muted"><i class="fas fa-clock me-1"></i>{{ $report->occurred }}</div>@endif
                            </td>
                            <td class="small">
                                {{ $report->reporter_name ?: 'Anonim' }}
                                @if ($report->contact)<div class="text-muted">{{ $report->contact }}</div>@endif
                                @if ($report->conversation)<a href="{{ route('admin.wa-inbox.show', $report->conversation) }}" class="small"><i class="fab fa-whatsapp me-1"></i>Buka chat</a>@endif
                            </td>
                            <td>
                                <form action="{{ route('admin.citizen-reports.update', $report) }}" method="POST" class="d-flex flex-column gap-1">
                                    @csrf
                                    @method('PUT')
                                    <select name="status" class="form-select form-select-sm">
                                        @foreach (App\Models\CitizenReport::STATUSES as $key => $label)
                                            <option value="{{ $key }}" @selected($report->status === $key)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <textarea name="officer_notes" rows="2" maxlength="2000" class="form-control form-control-sm" placeholder="Catatan petugas, mis. diteruskan ke Dinas PUPR 23/11">{{ $report->officer_notes }}</textarea>
                                    <button class="btn btn-sm btn-outline-primary align-self-end">Simpan</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-5">Belum ada laporan warga.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($reports->hasPages())<div class="p-3 border-top">{{ $reports->links() }}</div>@endif
    </div>
</div>
@endsection
