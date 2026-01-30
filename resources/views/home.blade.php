@extends('layouts.app')

@section('content')
<div class="p-5 mb-4 bg-light rounded-3">
    <div class="container-fluid py-5">
      <h1 class="display-5 fw-bold">Selamat Datang di PPID</h1>
      <p class="col-md-8 fs-4">Pejabat Pengelola Informasi dan Dokumentasi Kabupaten Empat Lawang. Kami berkomitmen memberikan layanan informasi publik yang transparan dan akuntabel.</p>
      <a href="{{ route('request.create') }}" class="btn btn-primary btn-lg" type="button">Ajukan Permohonan Informasi</a>
    </div>
</div>

<div class="row align-items-md-stretch">
    <div class="col-md-6">
      <div class="h-100 p-5 text-white bg-dark rounded-3">
        <h2>Cari Informasi Publik</h2>
        <p>Temukan informasi berkala, serta merta, dan setiap saat yang Anda butuhkan melalui portal kami.</p>
        <a href="{{ route('informasi-publik.index') }}" class="btn btn-outline-light" type="button">Lihat Data</a>
      </div>
    </div>
    <div class="col-md-6">
      <div class="h-100 p-5 bg-light border rounded-3">
        <h2>Laporan Penyelenggaraan</h2>
        <p>Akses laporan kinerja dan laporan keuangan pemerintah daerah secara terbuka.</p>
        <a href="{{ route('laporan.index') }}" class="btn btn-outline-secondary" type="button">Unduh Laporan</a>
      </div>
    </div>
</div>

<div class="row mt-5">
    <div class="col-12">
        <h3 class="border-bottom pb-2">Berita Terkini</h3>
        <!-- News Loop would go here -->
        <div class="alert alert-info">Belum ada berita terbaru.</div>
    </div>
</div>
@endsection
