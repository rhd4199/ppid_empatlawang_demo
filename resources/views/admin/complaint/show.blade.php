@extends('layouts.admin')

@section('title', 'Detail Keberatan: ' . $complaint->ticket_number)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="mb-3">
            <a href="{{ route('admin.complaints.index') }}" class="text-decoration-none text-secondary">
                <i class="fas fa-arrow-left me-2"></i> Kembali ke Daftar
            </a>
        </div>

        <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-white py-4 px-4 border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold text-danger">Tiket #{{ $complaint->ticket_number }}</h5>
                        <p class="mb-0 text-muted small">Diajukan pada {{ $complaint->created_at->format('d M Y, H:i') }} WIB</p>
                    </div>
                    <div>
                        @php
                            $statusClass = match($complaint->status) {
                                'pending' => 'bg-warning text-dark',
                                'processed' => 'bg-info text-white',
                                'resolved' => 'bg-success text-white',
                                'rejected' => 'bg-danger text-white',
                                default => 'bg-secondary text-white'
                            };
                            $statusLabel = match($complaint->status) {
                                'pending' => 'Menunggu',
                                'processed' => 'Diproses',
                                'resolved' => 'Diselesaikan',
                                'rejected' => 'Ditolak',
                                default => ucfirst($complaint->status)
                            };
                        @endphp
                        <span class="badge {{ $statusClass }} rounded-pill px-3 py-2 fs-6">
                            {{ $statusLabel }}
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="card-body p-4">
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <h6 class="fw-bold text-uppercase text-muted small mb-3">Data Pengaju</h6>
                        <table class="table table-borderless">
                            <tr>
                                <td class="ps-0 text-muted" style="width: 140px;">Nama Lengkap</td>
                                <td class="fw-bold">{{ $complaint->name }}</td>
                            </tr>
                            <tr>
                                <td class="ps-0 text-muted">Email</td>
                                <td><a href="mailto:{{ $complaint->email }}">{{ $complaint->email }}</a></td>
                            </tr>
                            <tr>
                                <td class="ps-0 text-muted">No. Telepon</td>
                                <td><a href="tel:{{ $complaint->phone }}">{{ $complaint->phone }}</a></td>
                            </tr>
                            @if($complaint->request_ticket_number)
                            <tr>
                                <td class="ps-0 text-muted">Ref. Permohonan</td>
                                <td><span class="badge bg-light text-dark border">{{ $complaint->request_ticket_number }}</span></td>
                            </tr>
                            @endif
                        </table>
                    </div>
                    <div class="col-md-6 mb-4">
                        <h6 class="fw-bold text-uppercase text-muted small mb-3">Detail Keberatan</h6>
                        <div class="mb-3">
                            <label class="form-label text-muted small">Alasan Keberatan</label>
                            <div class="p-3 bg-light rounded-3 border">
                                {{ $complaint->reason_complaint }}
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <h6 class="fw-bold text-uppercase text-primary small mb-3">Tindak Lanjut Admin</h6>
                <form action="{{ route('admin.complaints.update', $complaint->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Update Status</label>
                            <select name="status" class="form-select" required>
                                <option value="pending" {{ $complaint->status == 'pending' ? 'selected' : '' }}>Menunggu</option>
                                <option value="processed" {{ $complaint->status == 'processed' ? 'selected' : '' }}>Diproses</option>
                                <option value="resolved" {{ $complaint->status == 'resolved' ? 'selected' : '' }}>Diselesaikan</option>
                                <option value="rejected" {{ $complaint->status == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                            </select>
                        </div>
                        <div class="col-md-8 mb-3">
                            <label class="form-label fw-bold">Tanggapan Admin</label>
                            <textarea name="admin_reply" class="form-control" rows="4" placeholder="Berikan tanggapan atau hasil tindak lanjut...">{{ $complaint->admin_reply }}</textarea>
                            <div class="form-text">Tanggapan ini mungkin akan dikirimkan ke email pengaju (opsional).</div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                            <i class="fas fa-save me-2"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
