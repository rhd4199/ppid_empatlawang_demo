@extends('layouts.admin')

@section('title', 'Kelola Berita')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Kelola Berita</h1>
        <a href="{{ route('admin.news.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Tambah Berita
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Bulk Actions Toolbar (Hidden by default) -->
    <div id="bulk-action-toolbar" class="card mb-4 bg-primary text-white" style="display: none;">
        <div class="card-body d-flex justify-content-between align-items-center py-2">
            <div>
                <span id="selected-count" class="fw-bold">0</span> item terpilih
            </div>
            <div>
                <button type="button" class="btn btn-light btn-sm me-1" onclick="submitBulkAction('publish')">
                    <i class="fas fa-check me-1"></i> Publish
                </button>
                <button type="button" class="btn btn-warning btn-sm me-1" onclick="submitBulkAction('unpublish')">
                    <i class="fas fa-times me-1"></i> Unpublish
                </button>
                <button type="button" class="btn btn-danger btn-sm" onclick="submitBulkAction('delete')">
                    <i class="fas fa-trash me-1"></i> Hapus
                </button>
            </div>
        </div>
    </div>

    <form id="bulk-action-form" action="{{ route('admin.news.bulk-action') }}" method="POST" class="d-none">
        @csrf
        <input type="hidden" name="action" id="bulk_action_type">
        <div id="bulk_action_ids"></div>
    </form>

    <div class="row">
        @forelse($news as $item)
        <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
            <div class="card h-100 shadow-sm hover-shadow transition-all">
                <!-- Checkbox for Bulk Action -->
                <div class="position-absolute top-0 start-0 p-2 z-index-1">
                    <div class="form-check">
                        <input class="form-check-input border-2 shadow-sm bulk-checkbox" type="checkbox" value="{{ $item->id }}" style="transform: scale(1.2);">
                    </div>
                </div>

                <!-- Image -->
                <div class="ratio ratio-16x9 card-img-top overflow-hidden">
                    @if($item->image)
                        <img src="{{ Storage::url($item->image) }}" alt="{{ $item->title }}" class="object-fit-cover w-100 h-100">
                    @else
                        <div class="bg-light d-flex align-items-center justify-content-center text-muted h-100">
                            <i class="fas fa-image fa-3x"></i>
                        </div>
                    @endif
                </div>

                <div class="card-body d-flex flex-column">
                    <!-- Status Badge -->
                    <div class="mb-2">
                        @if($item->is_published)
                            <span class="badge bg-success">Published</span>
                        @else
                            <span class="badge bg-secondary">Draft</span>
                        @endif
                        
                        @if($item->published_at && \Carbon\Carbon::parse($item->published_at)->isFuture())
                            <span class="badge bg-info text-dark">Scheduled: {{ \Carbon\Carbon::parse($item->published_at)->format('d M Y') }}</span>
                        @endif
                    </div>

                    <h5 class="card-title text-truncate-2 fw-bold mb-2" title="{{ $item->title }}">
                        {{ $item->title }}
                    </h5>
                    
                    <p class="card-text text-muted small mb-3">
                        <i class="far fa-calendar-alt me-1"></i> {{ $item->created_at->format('d M Y') }}
                        <span class="mx-1">•</span>
                        <i class="far fa-user me-1"></i> {{ $item->author ?? 'Admin' }}
                    </p>

                    <div class="mt-auto d-flex justify-content-between align-items-center">
                        <div class="btn-group">
                            <a href="{{ route('admin.news.edit', $item->id) }}" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="confirmDelete({{ $item->id }}, '{{ $item->title }}')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        
                        <form action="{{ route('admin.news.toggle-status', $item->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-link p-0 text-decoration-none" title="Toggle Status">
                                <i class="fas fa-toggle-{{ $item->is_published ? 'on text-success' : 'off text-secondary' }} fa-2x"></i>
                            </button>
                        </form>
                        
                        <form action="{{ route('admin.news.toggle-headline', $item->id) }}" method="POST" class="d-inline ms-2">
                            @csrf
                            <button type="submit" class="btn btn-link p-0 text-decoration-none" title="{{ $item->is_headline ? 'Hapus dari Berita Utama' : 'Jadikan Berita Utama' }}">
                                <i class="fas fa-star {{ $item->is_headline ? 'text-warning' : 'text-secondary' }} fa-2x"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="alert alert-info text-center">
                Belum ada berita yang ditambahkan.
            </div>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-center mt-4">
        {{ $news->links() }}
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Apakah Anda yakin ingin menghapus berita "<span id="deleteItemTitle" class="fw-bold"></span>"?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form id="deleteForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Action Confirmation Modal -->
<div class="modal fade" id="bulkActionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Aksi Massal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Apakah Anda yakin ingin melakukan aksi <strong><span id="bulkActionName"></span></strong> pada <span id="bulkActionCount"></span> item yang dipilih?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="executeBulkAction()">Ya, Lanjutkan</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .text-truncate-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .hover-shadow:hover {
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
        transform: translateY(-2px);
    }
    .transition-all {
        transition: all 0.3s ease;
    }
    .z-index-1 {
        z-index: 1;
    }
</style>
@endpush

@push('scripts')
<script>
    // Bulk Action Logic
    const checkboxes = document.querySelectorAll('.bulk-checkbox');
    const toolbar = document.getElementById('bulk-action-toolbar');
    const selectedCountSpan = document.getElementById('selected-count');
    const bulkActionForm = document.getElementById('bulk-action-form');
    const bulkActionIdsContainer = document.getElementById('bulk_action_ids');
    const bulkActionTypeInput = document.getElementById('bulk_action_type');
    
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateToolbar);
    });

    function toggleSelectAll() {
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        checkboxes.forEach(cb => cb.checked = !allChecked);
        updateToolbar();
    }

    function updateToolbar() {
        const selected = document.querySelectorAll('.bulk-checkbox:checked');
        const count = selected.length;
        
        selectedCountSpan.textContent = count;
        
        if (count > 0) {
            toolbar.style.display = 'block';
        } else {
            toolbar.style.display = 'none';
        }
    }

    function submitBulkAction(action) {
        const selected = document.querySelectorAll('.bulk-checkbox:checked');
        if (selected.length === 0) return;

        const actionName = action === 'delete' ? 'Hapus' : (action === 'publish' ? 'Publikasikan' : 'Tarik Publikasi');
        document.getElementById('bulkActionName').textContent = actionName;
        document.getElementById('bulkActionCount').textContent = selected.length;
        
        // Store action type
        bulkActionTypeInput.value = action;
        
        // Show modal
        new bootstrap.Modal(document.getElementById('bulkActionModal')).show();
    }

    function executeBulkAction() {
        bulkActionIdsContainer.innerHTML = '';
        const selected = document.querySelectorAll('.bulk-checkbox:checked');
        
        selected.forEach(cb => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = cb.value;
            bulkActionIdsContainer.appendChild(input);
        });

        bulkActionForm.submit();
    }

    // Delete Modal Logic
    function confirmDelete(id, title) {
        const form = document.getElementById('deleteForm');
        form.action = `/admin/berita/${id}`;
        document.getElementById('deleteItemTitle').textContent = title;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }
</script>
@endpush
@endsection
