@extends('layouts.admin')

@section('title', 'Pengajuan Keberatan')

@section('content')
<div class="card border-0 shadow-lg rounded-4 overflow-hidden">
    <div class="card-header bg-gradient bg-danger text-white py-4">
        <h5 class="mb-1 fw-bold"><i class="fas fa-exclamation-circle me-2"></i>Daftar Pengajuan Keberatan</h5>
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
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-uppercase text-secondary small">
                    <tr>
                        <th class="px-4 py-3 border-0">Tiket</th>
                        <th class="px-4 py-3 border-0">Pengaju</th>
                        <th class="px-4 py-3 border-0">Alasan Keberatan</th>
                        <th class="px-4 py-3 border-0">Tanggal</th>
                        <th class="px-4 py-3 border-0 text-center">Status</th>
                        <th class="px-4 py-3 border-0 text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($complaints as $item)
                    <tr class="border-bottom">
                        <td class="px-4 py-3">
                            <span class="badge bg-light text-dark border">{{ $item->ticket_number }}</span>
                            @if($item->request_ticket_number)
                                <div class="small text-muted mt-1">Ref: {{ $item->request_ticket_number }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="fw-bold text-dark">{{ $item->name }}</div>
                            <div class="small text-muted"><i class="fas fa-envelope me-1"></i> {{ $item->email }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <small class="text-muted d-block text-truncate" style="max-width: 250px;">
                                {{ Str::limit($item->reason_complaint, 50) }}
                            </small>
                        </td>
                        <td class="px-4 py-3 text-muted small">
                            <div class="d-flex flex-column">
                                <span><i class="far fa-calendar me-1"></i> {{ $item->created_at->format('d M Y') }}</span>
                                <span class="text-xs text-muted mt-1">{{ $item->created_at->format('H:i') }} WIB</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $statusClass = match($item->status) {
                                    'pending' => 'bg-warning text-dark',
                                    'processed' => 'bg-info text-white',
                                    'resolved' => 'bg-success text-white',
                                    'rejected' => 'bg-danger text-white',
                                    default => 'bg-secondary text-white'
                                };
                                $statusLabel = match($item->status) {
                                    'pending' => 'Menunggu',
                                    'processed' => 'Diproses',
                                    'resolved' => 'Diselesaikan',
                                    'rejected' => 'Ditolak',
                                    default => ucfirst($item->status)
                                };
                            @endphp
                            <span class="badge {{ $statusClass }} rounded-pill px-3 py-2">
                                {{ $statusLabel }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-end">
                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" 
                                        class="btn btn-sm btn-outline-success rounded-circle shadow-sm" 
                                        style="width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center;"
                                        data-bs-toggle="modal"
                                        data-bs-target="#statusModal{{ $item->id }}"
                                        title="Update Status">
                                    <i class="fas fa-edit fa-xs"></i>
                                </button>
                                <a href="{{ route('admin.complaints.show', $item->id) }}" 
                                   class="btn btn-sm btn-outline-primary rounded-circle shadow-sm" 
                                   style="width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center;"
                                   title="Detail">
                                    <i class="fas fa-eye fa-xs"></i>
                                </a>
                                <button type="button" 
                                        class="btn btn-sm btn-outline-danger rounded-circle shadow-sm" 
                                        style="width: 32px; height: 32px; padding: 0;"
                                        data-bs-toggle="modal"
                                        data-bs-target="#deleteModal{{ $item->id }}"
                                        title="Hapus">
                                    <i class="fas fa-trash-alt fa-xs"></i>
                                </button>
                            </div>

                            <!-- Status Update Modal -->
                            <div class="modal fade text-start" id="statusModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                                        <div class="modal-header bg-light border-0">
                                            <h5 class="modal-title fw-bold">Update Status Keberatan</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="{{ route('admin.complaints.update', $item->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-body p-4">
                                                <div class="mb-3">
                                                    <label class="form-label small text-muted fw-bold text-uppercase">Status Saat Ini</label>
                                                    <select name="status" class="form-select">
                                                        <option value="pending" {{ $item->status == 'pending' ? 'selected' : '' }}>Menunggu</option>
                                                        <option value="processed" {{ $item->status == 'processed' ? 'selected' : '' }}>Diproses</option>
                                                        <option value="resolved" {{ $item->status == 'resolved' ? 'selected' : '' }}>Diselesaikan</option>
                                                        <option value="rejected" {{ $item->status == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small text-muted fw-bold text-uppercase">Tanggapan Admin (Opsional)</label>
                                                    <textarea name="admin_reply" class="form-control" rows="3" placeholder="Tambahkan tanggapan untuk pengaju...">{{ $item->admin_reply }}</textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-0 bg-light p-3">
                                                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Simpan Perubahan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Delete Confirmation Modal -->
                            <div class="modal fade text-start" id="deleteModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-sm">
                                    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                                        <div class="modal-body p-4 text-center">
                                            <div class="mb-3 text-danger">
                                                <i class="fas fa-exclamation-circle fa-3x"></i>
                                            </div>
                                            <h5 class="fw-bold mb-2">Hapus Pengajuan?</h5>
                                            <p class="text-muted small mb-4">Tiket <strong>{{ $item->ticket_number }}</strong> akan dihapus permanen.</p>
                                            
                                            <form action="{{ route('admin.complaints.destroy', $item->id) }}" method="POST">
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
                                    <i class="fas fa-inbox fa-4x"></i>
                                </div>
                                <h5 class="text-muted fw-bold">Belum ada pengajuan keberatan</h5>
                                <p class="text-muted small">Pengajuan keberatan baru akan muncul di sini.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="p-4 border-top">
            {{ $complaints->links() }}
        </div>
    </div>
</div>
@endsection
