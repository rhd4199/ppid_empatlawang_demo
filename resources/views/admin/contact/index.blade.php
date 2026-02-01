@extends('layouts.admin')

@section('title', 'Kotak Masuk')

@section('content')
<div class="container-fluid pb-5">
    <!-- Header & Stats -->
    <div class="row g-4 mb-4 align-items-center">
        <div class="col-md-6">
            <h1 class="h3 mb-0 text-gray-800 fw-bold">
                <i class="fas fa-inbox me-2 text-primary"></i>Kotak Masuk
            </h1>
        </div>
        <div class="col-md-6">
            <div class="d-flex justify-content-md-end gap-3">
                <div class="card shadow-sm border-0 bg-primary text-white" style="min-width: 150px;">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <div class="small text-white-50 text-uppercase fw-bold ls-1">Total Pesan</div>
                            <div class="h3 mb-0 fw-bold">{{ $messages->count() }}</div>
                        </div>
                        <i class="fas fa-envelope fa-2x text-white-50"></i>
                    </div>
                </div>
                <div class="card shadow-sm border-0 bg-white" style="min-width: 150px;">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <div class="small text-muted text-uppercase fw-bold ls-1">Belum Dibaca</div>
                            <div class="h3 mb-0 fw-bold text-danger">{{ $messages->where('is_read', 0)->count() }}</div>
                        </div>
                        <i class="fas fa-envelope-open-text fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filter (Optional) -->
    <!-- <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-2">
            <div class="input-group">
                <span class="input-group-text bg-white border-0"><i class="fas fa-search text-muted"></i></span>
                <input type="text" class="form-control border-0 bg-white" placeholder="Cari pesan berdasarkan nama, email, atau subjek...">
            </div>
        </div>
    </div> -->

    <!-- Message List -->
    <div class="card border-0 shadow-lg overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-black fw-bold"><b>Daftar Pesan Terkini</b></h6>
            <span class="badge bg-light text-primary border">{{ $messages->count() }} Pesan</span>
        </div>
        <div class="list-group list-group-flush">
            @forelse($messages as $message)
            <div class="list-group-item list-group-item-action p-4 border-bottom hover-bg-light transition-all message-item {{ !$message->is_read ? 'bg-blue-50' : '' }}" 
                 data-id="{{ $message->id }}" 
                 style="cursor: pointer;"
                 onclick="viewMessage(this)">
                <div class="row align-items-center g-3">
                    <!-- Avatar -->
                    <div class="col-auto">
                        <div class="avatar-circle {{ !$message->is_read ? 'bg-primary text-white shadow-sm' : 'bg-light text-secondary border' }} d-flex align-items-center justify-content-center fw-bold fs-5 rounded-circle" style="width: 50px; height: 50px;">
                            {{ strtoupper(substr($message->name, 0, 2)) }}
                        </div>
                    </div>
                    
                    <!-- Content -->
                    <div class="col">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <h6 class="mb-0 fw-bold text-dark {{ !$message->is_read ? 'fw-bolder' : '' }}">
                                {{ $message->name }}
                                @if(!$message->is_read)
                                <span class="badge bg-danger ms-2 badge-pill" id="badge-new-{{ $message->id }}">Baru</span>
                                @endif
                            </h6>
                            <small class="text-muted d-flex align-items-center">
                                <i class="far fa-clock me-1"></i> {{ $message->created_at->diffForHumans() }}
                            </small>
                        </div>
                        <div class="mb-1 text-dark fw-semibold">{{ $message->subject }}</div>
                        <p class="mb-0 text-muted small text-truncate" style="max-width: 80%;">
                            {{ $message->message }}
                        </p>
                    </div>

                    <!-- Actions -->
                    <div class="col-auto d-flex align-items-center gap-2" onclick="event.stopPropagation()">
                        <button class="btn btn-light btn-sm rounded-circle text-danger shadow-sm hover-scale" 
                                title="Hapus Pesan" 
                                onclick="deleteMessage({{ $message->id }})">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                        <i class="fas fa-chevron-right text-gray-300 ms-2"></i>
                    </div>
                </div>
            </div>

            <!-- Hidden Data for Modal -->
            <div id="data-{{ $message->id }}" class="d-none"
                 data-name="{{ $message->name }}"
                 data-email="{{ $message->email }}"
                 data-phone="{{ $message->phone ?? '-' }}"
                 data-subject="{{ $message->subject }}"
                 data-message="{{ $message->message }}"
                 data-date="{{ $message->created_at->format('d M Y, H:i') }}"
                 data-read="{{ $message->is_read }}">
            </div>
            @empty
            <div class="text-center py-5">
                <div class="mb-3">
                    <span class="fa-stack fa-2x text-gray-200">
                        <i class="fas fa-circle fa-stack-2x"></i>
                        <i class="fas fa-inbox fa-stack-1x fa-inverse"></i>
                    </span>
                </div>
                <h5 class="text-gray-500">Belum ada pesan masuk</h5>
                <p class="text-muted small">Pesan dari formulir kontak akan muncul di sini.</p>
            </div>
            @endforelse
        </div>
        @if($messages->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $messages->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Message Detail Modal -->
<div class="modal fade" id="messageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-light border-bottom-0">
                <h5 class="modal-title fw-bold text-primary"><i class="fas fa-envelope-open me-2"></i>Detail Pesan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-4">
                    <div class="d-flex align-items-center">
                        <div class="avatar-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold fs-4 rounded-circle me-3 shadow-sm" style="width: 60px; height: 60px;" id="modal-avatar">
                            AB
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold text-dark" id="modal-name">Nama Pengirim</h5>
                            <div class="text-muted small">
                                <i class="fas fa-envelope me-1"></i> <span id="modal-email">email@example.com</span>
                                <span class="mx-2">•</span>
                                <i class="fas fa-phone me-1"></i> <span id="modal-phone">08123456789</span>
                            </div>
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="badge bg-light text-dark border mb-1" id="modal-date">01 Jan 2024, 10:00</div>
                    </div>
                </div>

                <div class="card bg-light border-0 rounded-3 mb-4">
                    <div class="card-body">
                        <h6 class="fw-bold text-dark mb-2">Subjek: <span id="modal-subject" class="text-primary">Judul Pesan</span></h6>
                        <hr class="my-2 border-secondary opacity-10">
                        <p class="mb-0 text-dark" id="modal-message" style="white-space: pre-wrap; line-height: 1.6;">Isi pesan...</p>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Tutup</button>
                    <a href="#" id="modal-reply" class="btn btn-primary px-4"><i class="fas fa-reply me-2"></i>Balas via Email</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Form -->
<form id="delete-form" action="" method="POST" class="d-none">
    @csrf
    @method('DELETE')
</form>

@push('styles')
<style>
    .bg-blue-50 { background-color: #f0f7ff !important; }
    .hover-bg-light:hover { background-color: #f8f9fa !important; }
    .transition-all { transition: all 0.2s ease; }
    .hover-scale:hover { transform: scale(1.1); }
    .message-item { border-left: 4px solid transparent; }
    .message-item.bg-blue-50 { border-left-color: #4e73df; }
</style>
@endpush

@push('scripts')
<script>
    function viewMessage(element) {
        const id = element.getAttribute('data-id');
        const data = document.getElementById('data-' + id);
        
        // Populate Modal
        document.getElementById('modal-name').textContent = data.dataset.name;
        document.getElementById('modal-email').textContent = data.dataset.email;
        document.getElementById('modal-phone').textContent = data.dataset.phone;
        document.getElementById('modal-subject').textContent = data.dataset.subject;
        document.getElementById('modal-message').textContent = data.dataset.message;
        document.getElementById('modal-date').textContent = data.dataset.date;
        document.getElementById('modal-avatar').textContent = data.dataset.name.substring(0, 2).toUpperCase();
        
        document.getElementById('modal-reply').href = `mailto:${data.dataset.email}?subject=Re: ${data.dataset.subject}`;

        // Show Modal
        const modal = new bootstrap.Modal(document.getElementById('messageModal'));
        modal.show();

        // Mark as Read via AJAX if unread
        if (data.dataset.read == '0') {
            fetch(`{{ url('admin/pesan-masuk') }}/${id}/read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            }).then(response => {
                if (response.ok) {
                    // Update UI
                    element.classList.remove('bg-blue-50');
                    const badge = document.getElementById('badge-new-' + id);
                    if (badge) badge.remove();
                    element.querySelector('.avatar-circle').classList.remove('bg-primary', 'text-white', 'shadow-sm');
                    element.querySelector('.avatar-circle').classList.add('bg-light', 'text-secondary', 'border');
                    element.querySelector('h6').classList.remove('fw-bolder');
                    
                    // Update dataset
                    data.dataset.read = '1';
                }
            });
        }
    }

    function deleteMessage(id) {
        if (confirm('Apakah Anda yakin ingin menghapus pesan ini?')) {
            const form = document.getElementById('delete-form');
            form.action = `{{ url('admin/pesan-masuk') }}/${id}`;
            form.submit();
        }
    }
</script>
@endpush
@endsection