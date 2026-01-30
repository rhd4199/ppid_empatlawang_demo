@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">{{ __('Admin Dashboard') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="mb-4">
                        <h5>Selamat Datang, Admin!</h5>
                        <p>Anda telah masuk ke panel administrasi PPID Kabupaten Empat Lawang.</p>
                    </div>

                    <div class="list-group">
                        <a href="{{ route('news.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            Manajemen Berita
                            <span class="badge bg-primary rounded-pill">Lihat</span>
                        </a>
                        <a href="{{ route('informasi-publik.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            Manajemen Informasi Publik
                            <span class="badge bg-primary rounded-pill">Lihat</span>
                        </a>
                        <a href="{{ route('galleries.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            Manajemen Galeri
                            <span class="badge bg-primary rounded-pill">Lihat</span>
                        </a>
                        <a href="{{ route('request.create') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            Cek Permohonan Masuk
                            <span class="badge bg-primary rounded-pill">Cek</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
