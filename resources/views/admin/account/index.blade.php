@extends('layouts.admin')

@section('title', 'Pengaturan Akun')

@section('content')
<div class="container-fluid pb-5">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800 fw-bold">Pengaturan Akun</h1>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Profil & Keamanan</h6>
                </div>
                <div class="card-body p-4">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('admin.account.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Profile Info -->
                        <div class="mb-4">
                            <h6 class="text-uppercase text-muted small fw-bold mb-3 ls-1">Data Diri</h6>
                            <div class="mb-3">
                                <label for="name" class="form-label fw-semibold">Nama Lengkap</label>
                                <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold">Alamat Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                            </div>
                        </div>

                        <hr class="my-4 border-light">

                        <!-- Password Change -->
                        <div class="mb-4">
                            <h6 class="text-uppercase text-muted small fw-bold mb-3 ls-1">Ganti Password</h6>
                            <div class="alert alert-info py-2 small">
                                <i class="fas fa-info-circle me-1"></i> Kosongkan jika tidak ingin mengubah password.
                            </div>
                            
                            <div class="mb-3">
                                <label for="current_password" class="form-label fw-semibold">Password Saat Ini</label>
                                <input type="password" class="form-control" id="current_password" name="current_password" placeholder="Masukkan password lama untuk verifikasi">
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="password" class="form-label fw-semibold">Password Baru</label>
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Minimal 8 karakter">
                                </div>
                                <div class="col-md-6">
                                    <label for="password_confirmation" class="form-label fw-semibold">Konfirmasi Password Baru</label>
                                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Ulangi password baru">
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end pt-3">
                            <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">
                                <i class="fas fa-save me-2"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body p-4 text-center">
                    <div class="avatar-circle bg-white text-primary d-flex align-items-center justify-content-center fw-bold fs-1 rounded-circle mx-auto mb-3 shadow-sm" style="width: 100px; height: 100px;">
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    </div>
                    <h5 class="fw-bold mb-1">{{ $user->name }}</h5>
                    <p class="text-white-50 mb-3">{{ $user->email }}</p>
                    <div class="badge bg-white text-primary px-3 py-2 rounded-pill">Administrator</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
