@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Formulir Permohonan Informasi Publik</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('request.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">NIK / No. KTP</label>
                            <input type="text" name="nik" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Upload KTP (Scan/Foto)</label>
                            <input type="file" name="ktp_file" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea name="address" class="form-control" rows="2" required></textarea>
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
                        <label class="form-label">Rincian Informasi yang Dibutuhkan</label>
                        <textarea name="info_requested" class="form-control" rows="4" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tujuan Penggunaan Informasi</label>
                        <textarea name="reason" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Cara Mendapatkan Informasi</label>
                        <select name="delivery_method" class="form-select">
                            <option value="email">Kirim via Email</option>
                            <option value="pos">Kirim via Pos</option>
                            <option value="ambil_langsung">Ambil Langsung</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Kirim Permohonan</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
