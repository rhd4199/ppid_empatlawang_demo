@extends('layouts.admin')

@section('title', 'Perangkat Daerah')

@section('content')
<div class="card border-0 shadow-lg rounded-4 overflow-hidden">
    <div class="card-header bg-gradient bg-primary text-white py-4 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-1 fw-bold"><i class="fas fa-building me-2"></i>Perangkat Daerah (OPD)</h5>
            <small class="opacity-75">Profil & kontak OPD dan kecamatan, tampil di <a href="{{ route('agencies.index') }}" target="_blank" class="text-white">/instansi</a> dan dipakai Sapa Madani.</small>
        </div>
        <a href="{{ route('admin.agencies.create') }}" class="btn btn-light shadow-sm fw-bold text-primary rounded-pill px-4">
            <i class="fas fa-plus-circle me-2"></i> Tambah Instansi
        </a>
    </div>

    <div class="card-body p-0">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show m-4 shadow-sm border-0 border-start border-5 border-success" role="alert">
                <strong>Berhasil!</strong> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form method="GET" class="d-flex flex-wrap gap-2 p-3 border-bottom">
            <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm" style="max-width: 260px;" placeholder="Cari nama, singkatan, alamat...">
            <select name="kategori" class="form-select form-select-sm" style="max-width: 240px;" onchange="this.form.submit()">
                <option value="">Semua kategori</option>
                @foreach (\App\Models\Agency::CATEGORIES as $key => $label)
                    <option value="{{ $key }}" {{ request('kategori') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <button class="btn btn-sm btn-outline-primary">Cari</button>
            <span class="small text-muted align-self-center">Kontak bertanda <code>?</code> = 1 sumber, tidak tampil.</span>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-uppercase text-secondary small">
                    <tr>
                        @foreach (['name' => 'Instansi', 'category' => 'Kategori', 'confidence' => 'Kontak', 'checked_at' => 'Dicek'] as $field => $label)
                            <th class="px-4 py-3 border-0">
                                <a href="{{ request()->fullUrlWithQuery(['sort_field' => $field, 'sort_direction' => request('sort_direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-black">
                                    {{ $label }} <i class="fas fa-sort{{ request('sort_field') == $field ? (request('sort_direction') == 'asc' ? '-up' : '-down') : '' }} ms-1"></i>
                                </a>
                            </th>
                        @endforeach
                        <th class="px-4 py-3 border-0 text-center">Tampil</th>
                        <th class="px-4 py-3 border-0 text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse ($items as $item)
                    <tr class="border-bottom">
                        <td class="px-4 py-3">
                            <div class="fw-bold text-dark">{{ $item->name }}</div>
                            <small class="text-muted">{{ $item->short_name }}{{ $item->short_name && $item->head ? ' · ' : '' }}{{ $item->head }}</small>
                        </td>
                        <td class="px-4 py-3 small">{{ $item->category_label }}</td>
                        <td class="px-4 py-3 small">
                            <span class="badge bg-{{ ['high' => 'success', 'medium' => 'warning', 'low' => 'secondary'][$item->confidence] ?? 'light text-dark' }} rounded-pill">{{ \App\Models\Agency::CONFIDENCE[$item->confidence] ?? '-' }}</span>
                            <div class="text-muted mt-1">{{ count($item->public_phones) }} telp · {{ count($item->public_emails) }} email{{ $item->public_address ? ' · alamat' : '' }}</div>
                        </td>
                        <td class="px-4 py-3 text-muted small">{{ $item->checked_at?->format('d M Y') ?? '-' }}</td>
                        <td class="px-4 py-3 text-center">
                            <form action="{{ route('admin.agencies.toggle-status', $item->id) }}" method="POST">
                                @csrf
                                <div class="form-check form-switch d-inline-block">
                                    <input class="form-check-input" type="checkbox" role="switch" {{ $item->is_published ? 'checked' : '' }} onchange="this.form.submit()" style="cursor: pointer; width: 3em; height: 1.5em;" aria-label="Tampilkan di situs">
                                </div>
                                <div class="small mt-1 fw-bold {{ $item->is_published ? 'text-success' : 'text-secondary' }}">{{ $item->is_published ? 'Tampil' : 'Tersembunyi' }}</div>
                            </form>
                        </td>
                        <td class="px-4 py-3 text-end">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.agencies.edit', $item->id) }}" class="btn btn-sm btn-outline-primary rounded-circle" style="width: 32px; height: 32px; padding: 0; line-height: 30px;" title="Edit">
                                    <i class="fas fa-pen fa-xs"></i>
                                </a>
                                <form action="{{ route('admin.agencies.destroy', $item->id) }}" method="POST" onsubmit="return confirm(@js('Hapus "' . Str::limit($item->name, 40) . '"? Tindakan ini tidak bisa dibatalkan.'))">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle" style="width: 32px; height: 32px; padding: 0;" title="Hapus">
                                        <i class="fas fa-trash-alt fa-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <i class="fas fa-folder-open fa-4x text-muted opacity-25 mb-3"></i>
                            <h5 class="text-muted fw-bold">Belum ada data instansi</h5>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-3">{{ $items->links() }}</div>
    </div>
</div>
@endsection
