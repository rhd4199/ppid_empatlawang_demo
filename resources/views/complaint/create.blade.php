@extends('layouts.app')

@section('title', 'Pengajuan Keberatan')

@section('content')
<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1>Pengajuan Keberatan</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Pengajuan Keberatan</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="container pb-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card card-hover shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3">
                    <h4 class="mb-0 text-danger fw-bold"><i class="fas fa-exclamation-triangle me-2"></i> Formulir Pengajuan Keberatan Informasi</h4>
                </div>
                <div class="card-body p-4">
                    <div class="alert alert-warning mb-4 border-start border-5 border-warning">
                        <h5 class="alert-heading"><i class="fas fa-info-circle me-2"></i> Perhatian</h5>
                        <p class="mb-0">Gunakan formulir ini jika permohonan informasi Anda tidak ditanggapi sebagaimana mestinya, ditolak tanpa alasan yang jelas, atau tidak dipenuhi sesuai dengan peraturan perundang-undangan.</p>
                    </div>

                    <form action="{{ route('complaint.store') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label class="form-label fw-bold">Nomor Tiket Permohonan (Opsional)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-ticket-alt text-muted"></i></span>
                                <input type="text" name="request_ticket_number" class="form-control" placeholder="Contoh: REG-1721234567">
                            </div>
                            <div class="form-text">Masukkan nomor tiket jika sebelumnya sudah mengajukan permohonan melalui website ini.</div>
                        </div>

                        <hr class="my-4">

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" required placeholder="Masukkan nama lengkap Anda">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" required placeholder="contoh@email.com">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Nomor Telepon/HP <span class="text-danger">*</span></label>
                                <input type="text" name="phone" class="form-control" required placeholder="08xxxxxxxxxx">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Alasan Keberatan <span class="text-danger">*</span></label>
                            <textarea name="reason_complaint" class="form-control" rows="6" required placeholder="Jelaskan secara rinci alasan keberatan Anda..."></textarea>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="reset" class="btn btn-light me-md-2">Reset</button>
                            <button type="submit" class="btn btn-danger px-5"><i class="fas fa-paper-plane me-2"></i> Kirim Pengajuan Keberatan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
