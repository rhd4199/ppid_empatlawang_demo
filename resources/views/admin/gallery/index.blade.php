@extends('layouts.admin')

@section('title', 'Galeri Foto')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Galeri Foto</h1>
        <a href="{{ route('admin.galleries.create') }}" class="btn btn-primary">
            <i class="fas fa-plus fa-sm text-white-50"></i> Buat Album Baru
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Bulk Action Toolbar -->
    <div id="bulkActionToolbar" class="card shadow mb-4 border-left-primary" style="display: none;">
        <div class="card-body d-flex justify-content-between align-items-center py-2">
            <div>
                <span class="font-weight-bold text-primary mr-2">
                    <span id="selectedCount">0</span> Item Dipilih
                </span>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="selectAll(false)">Batal</button>
            </div>
            <div>
                <button type="button" class="btn btn-sm btn-success me-1" onclick="confirmBulkAction('publish')">
                    <i class="fas fa-check"></i> Publish
                </button>
                <button type="button" class="btn btn-sm btn-secondary me-1" onclick="confirmBulkAction('unpublish')">
                    <i class="fas fa-times"></i> Unpublish
                </button>
                <button type="button" class="btn btn-sm btn-danger" onclick="confirmBulkAction('delete')">
                    <i class="fas fa-trash"></i> Hapus
                </button>
            </div>
        </div>
    </div>

    <!-- Select All Checkbox -->
    <div class="mb-3 d-flex align-items-center">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="selectAllCheckbox" onchange="selectAll(this.checked)">
            <label class="form-check-label user-select-none" for="selectAllCheckbox">
                Pilih Semua
            </label>
        </div>
    </div>

    <form id="bulkActionForm" action="{{ route('admin.galleries.bulk-action') }}" method="POST">
        @csrf
        <input type="hidden" name="action" id="bulkActionInput">
        
        <div class="row">
            @forelse($galleries as $gallery)
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm {{ $gallery->is_published ? 'border-success' : '' }}">
                        <div class="position-relative">
                            <img src="{{ Storage::url($gallery->cover_image) }}" class="card-img-top" alt="{{ $gallery->title }}" style="height: 200px; object-fit: cover;">
                            
                            <!-- Checkbox Overlay -->
                            <div class="position-absolute top-0 start-0 p-2">
                                <div class="form-check">
                                    <input class="form-check-input item-checkbox" type="checkbox" name="ids[]" value="{{ $gallery->id }}" onchange="updateBulkToolbar()" style="transform: scale(1.5);">
                                </div>
                            </div>

                            <!-- Toggle Status Button -->
                            <div class="position-absolute top-0 end-0 p-2">
                                <button type="button" class="btn btn-sm {{ $gallery->is_published ? 'btn-success' : 'btn-secondary' }}" 
                                    title="Klik untuk mengubah status"
                                    onclick="confirmToggleStatus('{{ $gallery->id }}', '{{ $gallery->title }}', {{ $gallery->is_published ? 'true' : 'false' }})">
                                    {{ $gallery->is_published ? 'Published' : 'Draft' }}
                                </button>
                            </div>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title text-truncate" title="{{ $gallery->title }}">{{ $gallery->title }}</h5>
                            <p class="card-text small text-muted mb-2">
                                <i class="fas fa-images me-1"></i> {{ $gallery->items->count() }} Foto
                            </p>
                            <p class="card-text text-muted small flex-grow-1">
                                {{ Str::limit($gallery->description, 100) }}
                            </p>
                            <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                                <small class="text-muted">{{ $gallery->created_at->format('d M Y') }}</small>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm {{ $gallery->is_published ? 'btn-secondary' : 'btn-success' }}" 
                                        onclick="confirmToggleStatus('{{ $gallery->id }}', '{{ $gallery->title }}', {{ $gallery->is_published ? 'true' : 'false' }})"
                                        title="{{ $gallery->is_published ? 'Unpublish' : 'Publish' }}">
                                        <i class="fas {{ $gallery->is_published ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                    </button>
                                    <a href="{{ route('admin.galleries.edit', $gallery->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i> Kelola
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $gallery->id }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Delete Modal (Individual) -->
                <div class="modal fade" id="deleteModal{{ $gallery->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Hapus Album</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                Apakah Anda yakin ingin menghapus album <strong>{{ $gallery->title }}</strong>? Semua foto di dalamnya juga akan terhapus.
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <!-- We need to use form attribute or JS because this modal is inside the bulk form (wait, no, loops are tricky) -->
                                <!-- The bulk form wraps the row, so individual forms cannot be nested directly. -->
                                <!-- Solution: Move individual delete forms OUTSIDE the bulk form loop, or use JS to submit. -->
                                <!-- I'll use JS to submit a hidden form for individual delete to avoid nesting forms. -->
                                <button type="button" class="btn btn-danger" onclick="document.getElementById('delete-form-{{ $gallery->id }}').submit()">Hapus</button>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <div class="text-muted">
                        <i class="fas fa-images fa-3x mb-3"></i>
                        <p>Belum ada album galeri.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </form>

    <!-- Hidden Individual Delete Forms -->
    @foreach($galleries as $gallery)
        <form id="delete-form-{{ $gallery->id }}" action="{{ route('admin.galleries.destroy', $gallery->id) }}" method="POST" style="display: none;">
            @csrf
            @method('DELETE')
        </form>
        
        <form id="toggle-form-{{ $gallery->id }}" action="{{ route('admin.galleries.toggle-status', $gallery->id) }}" method="POST" style="display: none;">
            @csrf
        </form>
    @endforeach

    <div class="d-flex justify-content-center mt-4">
        {{ $galleries->links() }}
    </div>
</div>

<!-- Bulk Action Confirmation Modal -->
<div class="modal fade" id="bulkActionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkActionTitle">Konfirmasi Aksi Massal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="bulkActionMessage">Apakah Anda yakin ingin melakukan aksi ini?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="confirmBulkBtn">Ya, Lanjutkan</button>
            </div>
        </div>
    </div>
</div>

<!-- Toggle Status Confirmation Modal -->
<div class="modal fade" id="toggleStatusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Perubahan Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="toggleStatusMessage">Apakah Anda yakin ingin mengubah status album ini?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="confirmToggleBtn">Ya, Ubah</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Bulk Action Logic
    function selectAll(checked) {
        document.querySelectorAll('.item-checkbox').forEach(checkbox => {
            checkbox.checked = checked;
        });
        updateBulkToolbar();
    }

    function updateBulkToolbar() {
        const checkedCount = document.querySelectorAll('.item-checkbox:checked').length;
        const toolbar = document.getElementById('bulkActionToolbar');
        const selectedCountSpan = document.getElementById('selectedCount');
        const selectAllCheckbox = document.getElementById('selectAllCheckbox');

        selectedCountSpan.innerText = checkedCount;
        
        if (checkedCount > 0) {
            toolbar.style.display = 'block';
        } else {
            toolbar.style.display = 'none';
            selectAllCheckbox.checked = false;
        }
    }

    function confirmBulkAction(action) {
        const modal = new bootstrap.Modal(document.getElementById('bulkActionModal'));
        const title = document.getElementById('bulkActionTitle');
        const message = document.getElementById('bulkActionMessage');
        const confirmBtn = document.getElementById('confirmBulkBtn');
        const actionInput = document.getElementById('bulkActionInput');
        const form = document.getElementById('bulkActionForm');

        let actionText = '';
        let btnClass = 'btn-primary';

        switch(action) {
            case 'publish':
                actionText = 'mempublikasikan';
                btnClass = 'btn-success';
                break;
            case 'unpublish':
                actionText = 'meng-unpublish';
                btnClass = 'btn-secondary';
                break;
            case 'delete':
                actionText = 'menghapus';
                btnClass = 'btn-danger';
                break;
        }

        title.innerText = 'Konfirmasi ' + action.charAt(0).toUpperCase() + action.slice(1);
        message.innerHTML = `Apakah Anda yakin ingin <strong>${actionText}</strong> item yang dipilih?`;
        
        confirmBtn.className = 'btn ' + btnClass;
        confirmBtn.onclick = function() {
            actionInput.value = action;
            form.submit();
        };

        modal.show();
    }

    // Toggle Status Logic
    let toggleIdToSubmit = null;

    function confirmToggleStatus(id, title, isPublished) {
        const modal = new bootstrap.Modal(document.getElementById('toggleStatusModal'));
        const message = document.getElementById('toggleStatusMessage');
        const confirmBtn = document.getElementById('confirmToggleBtn');
        
        const action = isPublished ? 'meng-unpublish' : 'mempublikasikan';
        
        message.innerHTML = `Apakah Anda yakin ingin <strong>${action}</strong> album "<strong>${title}</strong>"?`;
        toggleIdToSubmit = id;
        
        confirmBtn.onclick = function() {
            document.getElementById('toggle-form-' + toggleIdToSubmit).submit();
        };

        modal.show();
    }
</script>
@endsection
