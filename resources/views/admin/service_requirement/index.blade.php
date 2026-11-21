@extends('layouts.admin')

@section('title', 'Persyaratan Layanan')

@section('content')
<div class="card border-0 shadow-lg rounded-4 overflow-hidden">
    <div class="card-header bg-gradient bg-primary text-white py-4 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-1 fw-bold"><i class="fas fa-clipboard-list me-2"></i>Persyaratan Layanan</h5>
            <small class="opacity-75">Info persyaratan layanan instansi (Disdukcapil, Samsat, BPN, dll).</small>
        </div>
        <a href="{{ route('admin.service-requirements.create') }}" class="btn btn-light shadow-sm fw-bold text-primary rounded-pill px-4">
            <i class="fas fa-plus-circle me-2"></i> Tambah Layanan
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
            <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm" style="max-width: 260px;" placeholder="Cari judul, instansi, kata kunci...">
            <select name="category" class="form-select form-select-sm" style="max-width: 200px;" onchange="this.form.submit()">
                <option value="">Semua kategori</option>
                @foreach ($categories as $key => $label)
                    <option value="{{ $key }}" {{ request('category') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <button class="btn btn-sm btn-outline-primary">Cari</button>
        </form>

        <form action="{{ route('admin.service-requirements.bulk-action') }}" method="POST" id="bulkActionForm">
            @csrf
            <input type="hidden" name="action" id="bulkActionInput">
            <div id="bulkActions" class="d-none bg-light p-3 border-bottom align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <span class="me-3 fw-bold text-primary"><span id="selectedCount">0</span> item terpilih</span>
                    <div class="btn-group shadow-sm" role="group">
                        <button type="button" class="btn btn-sm btn-success" onclick="bulkSubmit('publish', 'Publikasikan data terpilih?')"><i class="fas fa-check-circle me-1"></i> Publikasikan</button>
                        <button type="button" class="btn btn-sm btn-warning" onclick="bulkSubmit('unpublish', 'Tarik publikasi data terpilih?')"><i class="fas fa-eye-slash me-1"></i> Tarik Publikasi</button>
                        <button type="button" class="btn btn-sm btn-danger" onclick="bulkSubmit('delete', 'Hapus permanen data terpilih?')"><i class="fas fa-trash-alt me-1"></i> Hapus</button>
                    </div>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-uppercase text-secondary small">
                    <tr>
                        <th class="px-4 py-3 border-0" style="width: 40px;">
                            <input type="checkbox" class="form-check-input" id="selectAll" onchange="document.querySelectorAll('.item-checkbox').forEach(c => c.checked = this.checked); updateBulk()">
                        </th>
                        @foreach (['title' => 'Layanan', 'category' => 'Kategori', 'agency' => 'Instansi', 'checked_at' => 'Dicek'] as $field => $label)
                            <th class="px-4 py-3 border-0">
                                <a href="{{ request()->fullUrlWithQuery(['sort_field' => $field, 'sort_direction' => request('sort_direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-black">
                                    {{ $label }} <i class="fas fa-sort{{ request('sort_field') == $field ? (request('sort_direction') == 'asc' ? '-up' : '-down') : '' }} ms-1"></i>
                                </a>
                            </th>
                        @endforeach
                        <th class="px-4 py-3 border-0 text-center">Status</th>
                        <th class="px-4 py-3 border-0 text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse ($items as $item)
                    <tr class="border-bottom">
                        <td class="px-4 py-3">
                            <input type="checkbox" value="{{ $item->id }}" class="form-check-input item-checkbox" onchange="updateBulk()">
                        </td>
                        <td class="px-4 py-3">
                            <div class="fw-bold text-dark">{{ $item->title }}</div>
                            <small class="text-muted">{{ Str::limit($item->keywords, 60) }}</small>
                        </td>
                        <td class="px-4 py-3">
                            <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-2">{{ $item->category_label }}</span>
                        </td>
                        <td class="px-4 py-3">{{ $item->agency }}</td>
                        <td class="px-4 py-3 text-muted small">
                            {{ $item->checked_at?->format('d M Y') ?? '-' }}
                            @if ($item->source_url)
                                <a href="{{ $item->source_url }}" target="_blank" rel="noopener" title="Sumber"><i class="fas fa-external-link-alt ms-1"></i></a>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <form action="{{ route('admin.service-requirements.toggle-status', $item->id) }}" method="POST">
                                @csrf
                                <div class="form-check form-switch d-inline-block">
                                    <input class="form-check-input" type="checkbox" role="switch" {{ $item->is_published ? 'checked' : '' }} onchange="this.form.submit()" style="cursor: pointer; width: 3em; height: 1.5em;">
                                </div>
                                <div class="small mt-1 fw-bold {{ $item->is_published ? 'text-success' : 'text-secondary' }}">{{ $item->is_published ? 'Published' : 'Draft' }}</div>
                            </form>
                        </td>
                        <td class="px-4 py-3 text-end">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.service-requirements.edit', $item->id) }}" class="btn btn-sm btn-outline-primary rounded-circle" style="width: 32px; height: 32px; padding: 0; line-height: 30px;" title="Edit">
                                    <i class="fas fa-pen fa-xs"></i>
                                </a>
                                <form action="{{ route('admin.service-requirements.destroy', $item->id) }}" method="POST" onsubmit="return confirm(@js('Hapus layanan "' . Str::limit($item->title, 40) . '"? Tindakan ini tidak bisa dibatalkan.'))">
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
                        <td colspan="7" class="text-center py-5">
                            <i class="fas fa-folder-open fa-4x text-muted opacity-25 mb-3"></i>
                            <h5 class="text-muted fw-bold">Belum ada persyaratan layanan</h5>
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

@push('scripts')
<script>
    function updateBulk() {
        var count = document.querySelectorAll('.item-checkbox:checked').length;
        document.getElementById('selectedCount').innerText = count;
        document.getElementById('bulkActions').classList.toggle('d-none', count === 0);
        document.getElementById('bulkActions').classList.toggle('d-flex', count > 0);
    }

    function bulkSubmit(action, message) {
        var checked = document.querySelectorAll('.item-checkbox:checked');
        if (!checked.length || !confirm(message)) return;
        var form = document.getElementById('bulkActionForm');
        form.querySelectorAll('input[name="ids[]"]').forEach(i => i.remove());
        checked.forEach(function (c) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = c.value;
            form.appendChild(input);
        });
        document.getElementById('bulkActionInput').value = action;
        form.submit();
    }
</script>
@endpush
