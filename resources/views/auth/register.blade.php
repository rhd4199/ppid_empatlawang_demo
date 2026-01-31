@extends('layouts.app')

@section('title', 'Daftar - PPID Empat Lawang')

@section('content')
<div class="auth-container d-flex align-items-center justify-content-center py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-9">
                <div class="card border-0 shadow-lg overflow-hidden rounded-4">
                    <div class="row g-0">
                        <!-- Left Side - Visual -->
                        <div class="col-md-6 bg-primary text-white d-none d-md-flex align-items-center justify-content-center position-relative order-md-last">
                            <div class="bg-pattern position-absolute top-0 start-0 w-100 h-100 opacity-10"></div>
                            <div class="p-5 text-center position-relative" style="z-index: 1;">
                                <div class="mb-4">
                                    <i class="fas fa-users fa-4x text-warning"></i>
                                </div>
                                <h2 class="fw-bold mb-3 text-white">Bergabung Bersama Kami</h2>
                                <p class="lead mb-4 opacity-75">Dapatkan akses penuh ke layanan informasi publik.</p>
                                <hr class="border-white opacity-25 mx-auto w-25 mb-4">
                                <p class="small opacity-75">"Mewujudkan Pemerintahan yang Terbuka dan Akuntabel"</p>
                            </div>
                        </div>

                        <!-- Right Side - Form -->
                        <div class="col-md-6 bg-white order-md-first">
                            <div class="p-4 p-md-5">
                                <div class="text-center mb-4">
                                    <h3 class="fw-bold text-dark">Buat Akun Baru</h3>
                                    <p class="text-muted small">Lengkapi data diri Anda untuk mendaftar.</p>
                                </div>

                                <form method="POST" action="{{ route('register') }}">
                                    @csrf

                                    <div class="form-floating mb-3">
                                        <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus placeholder="Nama Lengkap">
                                        <label for="name"><i class="fas fa-user me-2 text-muted"></i>Nama Lengkap</label>
                                        @error('name')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>

                                    <div class="form-floating mb-3">
                                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="name@example.com">
                                        <label for="email"><i class="fas fa-envelope me-2 text-muted"></i>Alamat Email</label>
                                        @error('email')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>

                                    <div class="form-floating mb-3">
                                        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password" placeholder="Password">
                                        <label for="password"><i class="fas fa-lock me-2 text-muted"></i>Kata Sandi</label>
                                        @error('password')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>

                                    <div class="form-floating mb-4">
                                        <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password" placeholder="Konfirmasi Password">
                                        <label for="password-confirm"><i class="fas fa-lock me-2 text-muted"></i>Konfirmasi Kata Sandi</label>
                                    </div>

                                    <div class="d-grid mb-4">
                                        <button type="submit" class="btn btn-primary btn-lg rounded-pill shadow-sm">
                                            <i class="fas fa-user-plus me-2"></i> Daftar Sekarang
                                        </button>
                                    </div>

                                    <div class="text-center">
                                        <p class="small text-muted mb-0">Sudah memiliki akun? <a href="{{ route('login') }}" class="fw-bold text-primary text-decoration-none">Masuk disini</a></p>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <p class="small text-muted">&copy; {{ date('Y') }} Pemerintah Kabupaten Empat Lawang. All rights reserved.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .auth-container {
        min-height: calc(100vh - 200px);
        background-color: #f8f9fa;
    }
    .bg-pattern {
        background-image: radial-gradient(#ffffff 1px, transparent 1px);
        background-size: 20px 20px;
    }
    .form-floating > label {
        padding-left: 1.5rem;
    }
    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.25rem rgba(0, 77, 64, 0.15);
    }
    .btn-primary {
        background: linear-gradient(45deg, var(--primary-color), var(--accent-color));
        border: none;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 77, 64, 0.3);
    }
</style>
@endsection
