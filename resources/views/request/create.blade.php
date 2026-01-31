@extends('layouts.app')

@section('title', 'Permohonan Informasi')

@section('content')
<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1>Permohonan Informasi</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Permohonan Informasi</li>
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
                    <h4 class="mb-0 text-primary fw-bold"><i class="fas fa-edit me-2"></i> Formulir Permohonan Informasi Publik</h4>
                </div>
                <div class="card-body p-4">
                    <div class="alert alert-info mb-4 border-start border-5 border-info">
                        <i class="fas fa-info-circle me-2"></i> Silakan lengkapi formulir di bawah ini dengan data yang benar dan valid untuk mengajukan permohonan informasi publik.
                    </div>

                    <form action="{{ route('request.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" required placeholder="Masukkan nama lengkap sesuai KTP">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">NIK / No. KTP <span class="text-danger">*</span></label>
                                <input type="text" name="nik" class="form-control" required placeholder="16 digit NIK">
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

                        <div class="mb-3">
                            <label class="form-label fw-bold">Alamat Lengkap <span class="text-danger">*</span></label>
                            <textarea name="address" class="form-control" rows="2" required placeholder="Alamat lengkap sesuai domisili"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Upload KTP (Scan/Foto) <span class="text-danger">*</span></label>
                            <input type="file" name="ktp_file" class="form-control" accept="image/*,.pdf">
                            <div class="form-text">Format: JPG, PNG, PDF. Maksimal 2MB.</div>
                        </div>

                        <hr class="my-4">

                        <div class="mb-3">
                            <label class="form-label fw-bold">Rincian Informasi yang Dibutuhkan <span class="text-danger">*</span></label>
                            <textarea name="info_requested" class="form-control" rows="4" required placeholder="Deskripsikan informasi yang Anda butuhkan secara detail"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Tujuan Penggunaan Informasi <span class="text-danger">*</span></label>
                            <textarea name="reason" class="form-control" rows="3" required placeholder="Jelaskan untuk apa informasi ini akan digunakan"></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Cara Mendapatkan Informasi <span class="text-danger">*</span></label>
                            <select name="delivery_method" class="form-select">
                                <option value="email">Kirim via Email</option>
                                <option value="pos">Kirim via Pos</option>
                                <option value="ambil_langsung">Ambil Langsung</option>
                            </select>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="reset" class="btn btn-light me-md-2">Reset</button>
                            <button type="submit" class="btn btn-primary px-5"><i class="fas fa-paper-plane me-2"></i> Kirim Permohonan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
