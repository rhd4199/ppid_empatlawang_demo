@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-danger text-white">
                <h4 class="mb-0">Formulir Pengajuan Keberatan Informasi</h4>
            </div>
            <div class="card-body">
                <p class="alert alert-warning">
                    <i class="fas fa-info-circle me-1"></i> Gunakan formulir ini jika permohonan informasi Anda tidak ditanggapi sebagaimana mestinya atau ditolak.
                </p>

                <form action="{{ route('complaint.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Nomor Tiket Permohonan (Opsional)</label>
                        <input type="text" name="request_ticket_number" class="form-control" placeholder="Contoh: REG-1721234567">
                        <small class="text-muted">Masukkan nomor tiket jika sebelumnya sudah mengajukan permohonan melalui website ini.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nomor Telepon/HP</label>
                            <input type="text" name="phone" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alasan Keberatan</label>
                        <textarea name="reason_complaint" class="form-control" rows="5" required placeholder="Jelaskan secara rinci alasan keberatan Anda..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-danger w-100">Kirim Pengajuan Keberatan</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
