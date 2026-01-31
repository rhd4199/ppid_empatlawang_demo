@extends('layouts.admin')

@section('title', 'Detail Permohonan: ' . $request->ticket_number)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="mb-3">
            <a href="{{ route('admin.requests.index') }}" class="text-decoration-none text-secondary">
                <i class="fas fa-arrow-left me-2"></i> Kembali ke Daftar
            </a>
        </div>

        <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-white py-4 px-4 border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold text-primary">Tiket #{{ $request->ticket_number }}</h5>
                        <p class="mb-0 text-muted small">Diajukan pada {{ $request->created_at->format('d M Y, H:i') }} WIB</p>
                    </div>
                    <div>
                        @php
                            $statusClass = match($request->status) {
                                'pending' => 'bg-warning text-dark',
                                'processed' => 'bg-info text-white',
                                'approved' => 'bg-success text-white',
                                'rejected' => 'bg-danger text-white',
                                default => 'bg-secondary text-white'
                            };
                            $statusLabel = match($request->status) {
                                'pending' => 'Menunggu',
                                'processed' => 'Diproses',
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                                default => ucfirst($request->status)
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
                        <h6 class="fw-bold text-uppercase text-muted small mb-3">Data Pemohon</h6>
                        <table class="table table-borderless">
                            <tr>
                                <td class="ps-0 text-muted" style="width: 140px;">Nama Lengkap</td>
                                <td class="fw-bold">{{ $request->name }}</td>
                            </tr>
                            <tr>
                                <td class="ps-0 text-muted">NIK</td>
                                <td>{{ $request->nik }}</td>
                            </tr>
                            <tr>
                                <td class="ps-0 text-muted">Email</td>
                                <td><a href="mailto:{{ $request->email }}">{{ $request->email }}</a></td>
                            </tr>
                            <tr>
                                <td class="ps-0 text-muted">No. Telepon</td>
                                <td><a href="tel:{{ $request->phone }}">{{ $request->phone }}</a></td>
                            </tr>
                            <tr>
                                <td class="ps-0 text-muted">Alamat</td>
                                <td>{{ $request->address }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6 mb-4">
                        <h6 class="fw-bold text-uppercase text-muted small mb-3">Detail Informasi</h6>
                        <div class="mb-3">
                            <label class="form-label text-muted small">Informasi yang Dibutuhkan</label>
                            <div class="p-3 bg-light rounded-3 border">
                                {{ $request->info_requested }}
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small">Tujuan Penggunaan</label>
                            <div class="p-3 bg-light rounded-3 border">
                                {{ $request->reason }}
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small">Metode Penerimaan</label>
                            <div class="fw-bold">
                                @if($request->delivery_method == 'email')
                                    <i class="fas fa-envelope me-2 text-primary"></i> Email
                                @elseif($request->delivery_method == 'pos')
                                    <i class="fas fa-truck me-2 text-primary"></i> Pos
                                @else
                                    <i class="fas fa-walking me-2 text-primary"></i> Ambil Langsung
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                @if($request->ktp_file)
                <div class="mb-4">
                    <h6 class="fw-bold text-uppercase text-muted small mb-3">Dokumen KTP</h6>
                    <div class="card bg-light border-0">
                        <div class="card-body d-flex align-items-center">
                            <i class="fas fa-id-card fa-2x text-secondary me-3"></i>
                            <div>
                                <h6 class="mb-1">File KTP Pemohon</h6>
                                <a href="{{ asset('storage/' . $request->ktp_file) }}" target="_blank" class="text-primary text-decoration-none small fw-bold">
                                    <i class="fas fa-external-link-alt me-1"></i> Lihat / Unduh Dokumen
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <hr class="my-4">

                <h6 class="fw-bold text-uppercase text-primary small mb-3">Tindak Lanjut Admin</h6>
                <form action="{{ route('admin.requests.update', $request->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Update Status</label>
                            <select name="status" class="form-select" required>
                                <option value="pending" {{ $request->status == 'pending' ? 'selected' : '' }}>Menunggu</option>
                                <option value="processed" {{ $request->status == 'processed' ? 'selected' : '' }}>Diproses</option>
                                <option value="approved" {{ $request->status == 'approved' ? 'selected' : '' }}>Disetujui (Selesai)</option>
                                <option value="rejected" {{ $request->status == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                            </select>
                        </div>
                        <div class="col-md-8 mb-3">
                            <label class="form-label fw-bold">Catatan Admin</label>
                            <textarea name="admin_note" class="form-control" rows="3" placeholder="Tambahkan catatan atau balasan untuk pemohon...">{{ $request->admin_note }}</textarea>
                            <div class="form-text">Catatan ini mungkin akan terlihat oleh pemohon (opsional).</div>
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
