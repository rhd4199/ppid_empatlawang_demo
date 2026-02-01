@extends('layouts.admin')

@section('title', 'Manajemen Pengadaan Barang & Jasa')

@section('content')
<div class="card border-0 shadow-lg rounded-4 overflow-hidden">
    <div class="card-header bg-gradient bg-primary text-white py-4 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-1 fw-bold"><i class="fas fa-shopping-cart me-2"></i>Daftar Dokumen Pengadaan</h5>
        </div>
        <button type="button" class="btn btn-light shadow-sm fw-bold text-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#createModal">
            <i class="fas fa-plus-circle me-2"></i> Tambah Dokumen
        </button>
    </div>
    
    <div class="card-body p-0">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show m-4 shadow-sm border-0 border-start border-5 border-success" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle fa-2x me-3 text-success"></i>
                    <div>
                        <strong>Berhasil!</strong> {{ session('success') }}
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="table-responsive">
            <!-- Bulk Action Form -->
            <form action="{{ route('admin.procurements.bulk-action') }}" method="POST" id="bulkActionForm">
                @csrf
                <div id="bulkActions" class="d-none bg-light p-3 border-bottom d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <span class="me-3 fw-bold text-primary"><span id="selectedCount">0</span> item terpilih</span>
                        <input type="hidden" name="action" id="bulkActionInput">
                        <div class="btn-group shadow-sm me-2" role="group">
                            <button type="button" class="btn btn-sm btn-success text-white" onclick="confirmBulkAction('publish')">
                                <i class="fas fa-check-circle me-1"></i> Publikasikan
                            </button>
                            <button type="button" class="btn btn-sm btn-warning text-dark" onclick="confirmBulkAction('unpublish')">
                                <i class="fas fa-eye-slash me-1"></i> Tarik Publikasi
                            </button>
                            <button type="button" class="btn btn-sm btn-danger text-white" onclick="confirmBulkAction('delete')">
                                <i class="fas fa-trash-alt me-1"></i> Hapus
                            </button>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAll()">Batal</button>
                </div>
            </form>

            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-uppercase text-secondary small">
                    <tr>
                        <th class="px-4 py-3 border-0" style="width: 40px;">
                            <input type="checkbox" class="form-check-input" id="selectAll" onchange="toggleSelectAll(this)">
                        </th>
                        <th class="px-4 py-3 border-0">
                            <a href="{{ request()->fullUrlWithQuery(['sort_field' => 'title', 'sort_direction' => request('sort_direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-black">
                                Dokumen <i class="fas fa-sort{{ request('sort_field') == 'title' ? (request('sort_direction') == 'asc' ? '-up' : '-down') : '' }} ms-1"></i>
                            </a>
                        </th>
                        <th class="px-4 py-3 border-0">
                            <a href="{{ request()->fullUrlWithQuery(['sort_field' => 'category', 'sort_direction' => request('sort_direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-black">
                                Kategori <i class="fas fa-sort{{ request('sort_field') == 'category' ? (request('sort_direction') == 'asc' ? '-up' : '-down') : '' }} ms-1"></i>
                            </a>
                        </th>
                        <th class="px-4 py-3 border-0">
                            <a href="{{ request()->fullUrlWithQuery(['sort_field' => 'created_at', 'sort_direction' => request('sort_direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-black">
                                Tanggal <i class="fas fa-sort{{ request('sort_field') == 'created_at' ? (request('sort_direction') == 'asc' ? '-up' : '-down') : '' }} ms-1"></i>
                            </a>
                        </th>
                        <th class="px-4 py-3 border-0 text-center">
                            <a href="{{ request()->fullUrlWithQuery(['sort_field' => 'is_published', 'sort_direction' => request('sort_direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-black">
                                Status <i class="fas fa-sort{{ request('sort_field') == 'is_published' ? (request('sort_direction') == 'asc' ? '-up' : '-down') : '' }} ms-1"></i>
                            </a>
                        </th>
                        <th class="px-4 py-3 border-0 text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($documents as $item)
                    <tr class="border-bottom">
                        <td class="px-4 py-3">
                            <input type="checkbox" name="ids[]" value="{{ $item->id }}" class="form-check-input item-checkbox" onchange="updateBulkActionState()">
                        </td>
                        <td class="px-4 py-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3 me-3">
                                    <i class="far fa-file-alt fa-lg"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark mb-1">{{ $item->title }}</div>
                                    <small class="text-muted d-block text-truncate" style="max-width: 300px;">
                                        {{ Str::limit($item->description ?? 'Tidak ada deskripsi', 60) }}
                                    </small>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $categoryName = str_replace('pengadaan_', '', $item->category);
                                $categoryName = ucwords(str_replace('_', ' ', $categoryName));
                                
                                $badgeClass = match($item->category) {
                                    'pengadaan_info' => 'bg-info text-info',
                                    'pengadaan_regulasi' => 'bg-dark text-dark',
                                    default => 'bg-secondary text-secondary'
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }} bg-opacity-10 border border-opacity-25 rounded-pill px-3 py-2">
                                {{ $categoryName }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-muted small">
                            <div class="d-flex flex-column">
                                <span><i class="far fa-calendar me-1"></i> {{ $item->created_at->format('d M Y') }}</span>
                                <span class="text-xs text-muted mt-1">{{ $item->created_at->format('H:i') }} WIB</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                             <div class="small mt-1 fw-bold {{ $item->is_published ? 'text-success' : 'text-secondary' }}">
                                {{ $item->is_published ? 'Published' : 'Draft' }}
                            </div>
                        </td>
                        <td class="px-4 py-3 text-end">
                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" 
                                        class="btn btn-sm btn-outline-primary rounded-circle shadow-sm" 
                                        style="width: 32px; height: 32px; padding: 0;"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editModal{{ $item->id }}"
                                        title="Edit">
                                    <i class="fas fa-pen fa-xs"></i>
                                </button>
                                <button type="button" 
                                        class="btn btn-sm btn-outline-danger rounded-circle shadow-sm" 
                                        style="width: 32px; height: 32px; padding: 0;"
                                        data-bs-toggle="modal"
                                        data-bs-target="#deleteModal{{ $item->id }}"
                                        title="Hapus">
                                    <i class="fas fa-trash-alt fa-xs"></i>
                                </button>
                            </div>

                            <!-- Edit Modal -->
                            <div class="modal fade text-start" id="editModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow-lg rounded-4">
                                        <div class="modal-header bg-light border-bottom-0 py-3">
                                            <h5 class="modal-title fw-bold text-primary"><i class="fas fa-edit me-2"></i>Edit Dokumen</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="{{ route('admin.procurements.update', $item->id) }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-body p-4">
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold small text-uppercase text-muted">Judul Dokumen</label>
                                                    <input type="text" class="form-control bg-light border-0" name="title" value="{{ $item->title }}" required>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label fw-bold small text-uppercase text-muted">Kategori</label>
                                                    <select class="form-select bg-light border-0" name="category" required>
                                                        <option value="pengadaan_info" {{ $item->category == 'pengadaan_info' ? 'selected' : '' }}>Informasi Pengadaan</option>
                                                        <option value="pengadaan_regulasi" {{ $item->category == 'pengadaan_regulasi' ? 'selected' : '' }}>Regulasi/Aturan</option>
                                                    </select>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label fw-bold small text-uppercase text-muted">Deskripsi</label>
                                                    <textarea class="form-control bg-light border-0" name="description" rows="3">{{ $item->description }}</textarea>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label fw-bold small text-uppercase text-muted">Status</label>
                                                    <select class="form-select bg-light border-0" name="is_published" required>
                                                        <option value="1" {{ $item->is_published ? 'selected' : '' }}>Published</option>
                                                        <option value="0" {{ !$item->is_published ? 'selected' : '' }}>Draft</option>
                                                    </select>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label fw-bold small text-uppercase text-muted">File Dokumen</label>
                                                    @if($item->file_path)
                                                        <div class="card bg-success bg-opacity-10 border-success border-opacity-25 mb-2">
                                                            <div class="card-body p-3 d-flex align-items-center">
                                                                <div class="bg-success text-white rounded-circle p-2 me-3">
                                                                    <i class="fas fa-check"></i>
                                                                </div>
                                                                <div class="flex-grow-1">
                                                                    <h6 class="mb-0 fw-bold text-success">File Tersedia</h6>
                                                                    <small class="text-muted">Dokumen sudah diupload.</small>
                                                                </div>
                                                                <a href="{{ asset('storage/' . $item->file_path) }}" target="_blank" class="btn btn-sm btn-success rounded-pill px-3">
                                                                    <i class="fas fa-eye me-1"></i> Preview
                                                                </a>
                                                            </div>
                                                        </div>
                                                    @endif
                                                    <input class="form-control" type="file" name="file_path">
                                                    <div class="form-text small"><i class="fas fa-info-circle me-1"></i> Upload file baru untuk mengganti yang lama. Max 10MB.</div>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-top-0 pt-0 pb-4 px-4">
                                                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">Simpan Perubahan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Delete Confirmation Modal -->
                            <div class="modal fade" id="deleteModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-sm">
                                    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                                        <div class="modal-body p-4 text-center">
                                            <div class="mb-3 text-danger">
                                                <i class="fas fa-exclamation-circle fa-3x"></i>
                                            </div>
                                            <h5 class="fw-bold mb-2">Hapus Dokumen?</h5>
                                            <p class="text-muted small mb-4">Dokumen "<strong>{{ Str::limit($item->title, 20) }}</strong>" akan dihapus permanen. Tindakan ini tidak bisa dibatalkan.</p>
                                            
                                            <form action="{{ route('admin.procurements.destroy', $item->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <div class="d-grid gap-2">
                                                    <button type="submit" class="btn btn-danger rounded-pill fw-bold">Ya, Hapus!</button>
                                                    <button type="button" class="btn btn-light rounded-pill text-muted" data-bs-dismiss="modal">Batal</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="py-5">
                                <div class="mb-3 text-muted opacity-25">
                                    <i class="fas fa-folder-open fa-4x"></i>
                                </div>
                                <h5 class="text-muted fw-bold">Belum ada dokumen</h5>
                                <p class="text-muted small mb-0">Silakan tambahkan dokumen pengadaan baru.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-light border-bottom-0 py-3">
                <h5 class="modal-title fw-bold text-primary"><i class="fas fa-plus-circle me-2"></i>Tambah Dokumen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.procurements.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase text-muted">Judul Dokumen <span class="text-danger">*</span></label>
                        <input type="text" class="form-control bg-light border-0" name="title" required placeholder="Contoh: Pengumuman Lelang X">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase text-muted">Kategori <span class="text-danger">*</span></label>
                        <select class="form-select bg-light border-0" name="category" required>
                            <option value="" disabled selected>Pilih Kategori</option>
                            <option value="pengadaan_info">Informasi Pengadaan</option>
                            <option value="pengadaan_regulasi">Regulasi/Aturan</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase text-muted">Deskripsi</label>
                        <textarea class="form-control bg-light border-0" name="description" rows="3" placeholder="Deskripsi singkat dokumen..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase text-muted">Status</label>
                        <select class="form-select bg-light border-0" name="is_published">
                            <option value="1" selected>Published</option>
                            <option value="0">Draft</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase text-muted">File Dokumen</label>
                        <input class="form-control" type="file" name="file_path">
                        <div class="form-text small"><i class="fas fa-info-circle me-1"></i> Max 10MB. Format: PDF, DOC, XLS, JPG, PNG.</div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0 pb-4 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function toggleSelectAll(source) {
        const checkboxes = document.querySelectorAll('.item-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = source.checked;
        });
        updateBulkActionState();
    }

    function updateBulkActionState() {
        const checkboxes = document.querySelectorAll('.item-checkbox:checked');
        const bulkActions = document.getElementById('bulkActions');
        const selectedCount = document.getElementById('selectedCount');
        
        selectedCount.textContent = checkboxes.length;
        
        if (checkboxes.length > 0) {
            bulkActions.classList.remove('d-none');
        } else {
            bulkActions.classList.add('d-none');
            document.getElementById('selectAll').checked = false;
        }
    }

    function confirmBulkAction(action) {
        if (confirm('Apakah Anda yakin ingin melakukan tindakan ini pada item yang dipilih?')) {
            document.getElementById('bulkActionInput').value = action;
            document.getElementById('bulkActionForm').requestSubmit();
        }
    }

    function deselectAll() {
        const checkboxes = document.querySelectorAll('.item-checkbox');
        checkboxes.forEach(checkbox => checkbox.checked = false);
        document.getElementById('selectAll').checked = false;
        updateBulkActionState();
    }
</script>
@endpush
@endsection

@push('scripts')
@if(session('open_create_modal'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var myModal = new bootstrap.Modal(document.getElementById('createModal'));
        myModal.show();
    });
</script>
@endif
@endpush
