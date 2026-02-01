@extends('layouts.admin')

@section('title', 'Manajemen Laporan Kinerja')

@section('content')
<div class="card border-0 shadow-lg rounded-4 overflow-hidden">
    <div class="card-header bg-gradient bg-primary text-white py-4 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-1 fw-bold"><i class="fas fa-file-contract me-2"></i>Laporan Kinerja</h5>
        </div>
        <button type="button" class="btn btn-light shadow-sm fw-bold text-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#createModal">
            <i class="fas fa-plus-circle me-2"></i> Tambah Laporan
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
            <form action="{{ route('admin.reports.bulk-action') }}" method="POST" id="bulkActionForm">
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
                                    <i class="far fa-file-pdf fa-lg"></i>
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
                                $badgeClass = match($item->category) {
                                    'laporan_pemda' => 'bg-info text-info',
                                    'laporan_ppid' => 'bg-success text-success',
                                    default => 'bg-secondary text-secondary'
                                };
                                $categoryLabel = match($item->category) {
                                    'laporan_pemda' => 'Laporan Pemda',
                                    'laporan_ppid' => 'Laporan PPID',
                                    default => $item->category
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }} bg-opacity-10 border border-opacity-25 rounded-pill px-3 py-2">
                                {{ $categoryLabel }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-muted small">
                            <div class="d-flex flex-column">
                                <span><i class="far fa-calendar me-1"></i> {{ $item->created_at->format('d M Y') }}</span>
                                <span class="text-xs text-muted mt-1">{{ $item->created_at->format('H:i') }} WIB</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <form action="{{ route('admin.reports.toggle-status', $item->id) }}" method="POST">
                                @csrf
                                <div class="form-check form-switch d-inline-block">
                                    <input class="form-check-input shadow-sm" type="checkbox" role="switch" 
                                           id="statusSwitch{{ $item->id }}" 
                                           {{ $item->is_published ? 'checked' : '' }} 
                                           onchange="this.form.submit()"
                                           style="cursor: pointer; width: 3em; height: 1.5em;">
                                </div>
                                <div class="small mt-1 fw-bold {{ $item->is_published ? 'text-success' : 'text-secondary' }}">
                                    {{ $item->is_published ? 'Published' : 'Draft' }}
                                </div>
                            </form>
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
                                            <h5 class="modal-title fw-bold text-primary"><i class="fas fa-edit me-2"></i>Edit Laporan</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="{{ route('admin.reports.update', $item->id) }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-body p-4">
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold small text-uppercase text-muted">Judul Laporan</label>
                                                    <input type="text" class="form-control bg-light border-0" name="title" value="{{ $item->title }}" required>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label fw-bold small text-uppercase text-muted">Kategori</label>
                                                    <select class="form-select bg-light border-0" name="category" required>
                                                        <option value="laporan_pemda" {{ $item->category == 'laporan_pemda' ? 'selected' : '' }}>Laporan Pemkab Empat Lawang</option>
                                                        <option value="laporan_ppid" {{ $item->category == 'laporan_ppid' ? 'selected' : '' }}>Laporan PPID</option>
                                                    </select>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label fw-bold small text-uppercase text-muted">Deskripsi</label>
                                                    <textarea class="form-control bg-light border-0" name="description" rows="3">{{ $item->description }}</textarea>
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
                                            <h5 class="fw-bold mb-2">Hapus Laporan?</h5>
                                            <p class="text-muted small mb-4">Laporan "<strong>{{ Str::limit($item->title, 20) }}</strong>" akan dihapus permanen. Tindakan ini tidak bisa dibatalkan.</p>
                                            
                                            <form action="{{ route('admin.reports.destroy', $item->id) }}" method="POST">
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
                                <h5 class="text-muted fw-bold">Belum ada laporan</h5>
                                <p class="text-muted small">Silakan tambahkan dokumen laporan kinerja baru.</p>
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
            <div class="modal-header bg-primary text-white border-bottom-0 py-3">
                <h5 class="modal-title fw-bold"><i class="fas fa-plus-circle me-2"></i>Tambah Laporan Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.reports.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase text-muted">Judul Laporan</label>
                        <input type="text" class="form-control bg-light border-0" name="title" placeholder="Contoh: Laporan Kinerja 2024" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase text-muted">Kategori</label>
                        <select class="form-select bg-light border-0" name="category" required>
                            <option value="" selected disabled>Pilih Kategori...</option>
                            <option value="laporan_pemda">Laporan Pemkab Empat Lawang</option>
                            <option value="laporan_ppid">Laporan PPID</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase text-muted">Deskripsi</label>
                        <textarea class="form-control bg-light border-0" name="description" rows="3" placeholder="Deskripsi singkat laporan..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase text-muted">File Dokumen</label>
                        <input class="form-control" type="file" name="file_path" required>
                        <div class="form-text small"><i class="fas fa-info-circle me-1"></i> Format: PDF, DOC, DOCX, XLS. Max 10MB.</div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0 pb-4 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">Simpan Laporan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Action Confirmation Modal -->
<div class="modal fade" id="bulkActionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-body p-4 text-center">
                <div class="mb-3 text-primary" id="bulkModalIcon">
                    <i class="fas fa-question-circle fa-3x"></i>
                </div>
                <h5 class="fw-bold mb-2" id="bulkModalTitle">Konfirmasi</h5>
                <p class="text-muted small mb-4" id="bulkModalBody">Apakah Anda yakin?</p>
                
                <div class="d-grid gap-2">
                    <button type="button" id="bulkModalConfirmBtn" class="btn btn-primary rounded-pill fw-bold">Ya, Lanjutkan</button>
                    <button type="button" class="btn btn-light rounded-pill text-muted" data-bs-dismiss="modal">Batal</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleSelectAll(source) {
        const checkboxes = document.querySelectorAll('.item-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = source.checked;
        });
        updateBulkActionState();
    }

    function deselectAll() {
        document.getElementById('selectAll').checked = false;
        const checkboxes = document.querySelectorAll('.item-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
        updateBulkActionState();
    }

    function updateBulkActionState() {
        const checkboxes = document.querySelectorAll('.item-checkbox:checked');
        const bulkActions = document.getElementById('bulkActions');
        const selectedCount = document.getElementById('selectedCount');
        
        selectedCount.innerText = checkboxes.length;

        if (checkboxes.length > 0) {
            bulkActions.classList.remove('d-none');
        } else {
            bulkActions.classList.add('d-none');
            document.getElementById('selectAll').checked = false;
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        var bulkForm = document.getElementById('bulkActionForm');
        if(bulkForm) {
            bulkForm.addEventListener('submit', function(e) {
                var checkboxes = document.querySelectorAll('.item-checkbox:checked');
                if (checkboxes.length === 0) {
                    e.preventDefault();
                    return;
                }
                
                // Remove old hidden inputs
                var oldInputs = this.querySelectorAll('input[name="ids[]"]');
                oldInputs.forEach(input => input.remove());

                // Add new hidden inputs
                checkboxes.forEach(function(checkbox) {
                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'ids[]';
                    input.value = checkbox.value;
                    this.appendChild(input);
                }.bind(this));
            });
        }
    });
    
    function confirmBulkAction(action) {
        let title = '';
        let message = '';
        let btnClass = '';
        let btnText = '';
        let iconClass = '';
        let iconColor = '';

        if(action === 'publish') {
            title = 'Publikasikan Laporan';
            message = 'Apakah Anda yakin ingin mempublikasikan laporan yang dipilih?';
            btnClass = 'btn-success';
            btnText = 'Ya, Publikasikan';
            iconClass = 'fas fa-check-circle';
            iconColor = 'text-success';
        } else if (action === 'unpublish') {
            title = 'Tarik Publikasi';
            message = 'Apakah Anda yakin ingin menarik publikasi laporan yang dipilih?';
            btnClass = 'btn-warning';
            btnText = 'Ya, Tarik Publikasi';
            iconClass = 'fas fa-eye-slash';
            iconColor = 'text-warning';
        } else if (action === 'delete') {
            title = 'Hapus Laporan';
            message = 'Apakah Anda yakin ingin menghapus laporan yang dipilih? Tindakan ini tidak bisa dibatalkan.';
            btnClass = 'btn-danger';
            btnText = 'Ya, Hapus';
            iconClass = 'fas fa-trash-alt';
            iconColor = 'text-danger';
        }

        // Set modal content
        document.getElementById('bulkModalTitle').innerText = title;
        document.getElementById('bulkModalBody').innerText = message;
        
        const confirmBtn = document.getElementById('bulkModalConfirmBtn');
        confirmBtn.className = 'btn rounded-pill fw-bold ' + btnClass;
        confirmBtn.innerText = btnText;
        
        const iconDiv = document.getElementById('bulkModalIcon');
        iconDiv.className = 'mb-3 ' + iconColor;
        iconDiv.innerHTML = '<i class="' + iconClass + ' fa-3x"></i>';
        
        // Set action
        document.getElementById('bulkActionInput').value = action;
        
        // Show modal
        var modal = new bootstrap.Modal(document.getElementById('bulkActionModal'));
        modal.show();
        
        // Handle confirm click
        confirmBtn.onclick = function() {
            document.getElementById('bulkActionForm').requestSubmit();
        };
    }
</script>
@endsection
