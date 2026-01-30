@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1>Hubungi Kami</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Kontak</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="container pb-5">
    <div class="row g-5">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100 bg-primary text-white">
                <div class="card-body p-4 p-md-5">
                    <h3 class="mb-4 text-white">Informasi Kontak</h3>
                    <p class="mb-5 opacity-75">Silakan hubungi kami melalui kontak di bawah ini atau kunjungi kantor kami pada jam kerja.</p>
                    
                    <div class="d-flex mb-4">
                        <div class="flex-shrink-0">
                            <i class="fas fa-map-marker-alt fa-lg opacity-75"></i>
                        </div>
                        <div class="ms-3">
                            <h5 class="text-white mb-1">Alamat Kantor</h5>
                            <p class="mb-0 opacity-75">Jl. Lintas Sumatera No. 1, Tebing Tinggi, Kab. Empat Lawang, Sumatera Selatan</p>
                        </div>
                    </div>

                    <div class="d-flex mb-4">
                        <div class="flex-shrink-0">
                            <i class="fas fa-phone-alt fa-lg opacity-75"></i>
                        </div>
                        <div class="ms-3">
                            <h5 class="text-white mb-1">Telepon</h5>
                            <p class="mb-0 opacity-75">(0702) 123456</p>
                        </div>
                    </div>

                    <div class="d-flex mb-4">
                        <div class="flex-shrink-0">
                            <i class="fas fa-envelope fa-lg opacity-75"></i>
                        </div>
                        <div class="ms-3">
                            <h5 class="text-white mb-1">Email</h5>
                            <p class="mb-0 opacity-75">ppid@empatlawangkab.go.id</p>
                        </div>
                    </div>

                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-clock fa-lg opacity-75"></i>
                        </div>
                        <div class="ms-3">
                            <h5 class="text-white mb-1">Jam Operasional</h5>
                            <p class="mb-0 opacity-75">Senin - Jumat: 08:00 - 16:00 WIB</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4 p-md-5">
                    <h3 class="mb-4">Kirim Pesan</h3>
                    <form action="{{ route('contact.store') }}" method="POST" id="contact-form">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="name" name="name" placeholder="Nama Lengkap" required>
                                    <label for="name">Nama Lengkap</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="email" class="form-control" id="email" name="email" placeholder="Alamat Email" required>
                                    <label for="email">Alamat Email</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="subject" name="subject" placeholder="Subjek Pesan" required>
                                    <label for="subject">Subjek Pesan</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating">
                                    <textarea class="form-control" placeholder="Tulis pesan Anda di sini" id="message" name="message" style="height: 150px" required></textarea>
                                    <label for="message">Pesan Anda</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5" id="submit-btn">
                                    <i class="fas fa-paper-plane me-2"></i> Kirim Pesan
                                </button>
                            </div>
                        </div>
                    </form>
                    <div id="form-message" class="mt-3" style="display: none;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Map -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card border-0 shadow-sm overflow-hidden">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3983.5!2d103.0!3d-3.7!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zM8KwNDInMDAuMCJTIDEwM8KwMDAnMDAuMCJF!5e0!3m2!1sen!2sid!4v1620000000000!5m2!1sen!2sid" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $('#contact-form').submit(function(e) {
        e.preventDefault();
        let btn = $('#submit-btn');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Mengirim...');
        
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                $('#form-message').removeClass('alert-danger').addClass('alert alert-success').html(response.success).show();
                $('#contact-form')[0].reset();
                btn.prop('disabled', false).html('<i class="fas fa-paper-plane me-2"></i> Kirim Pesan');
            },
            error: function() {
                $('#form-message').removeClass('alert-success').addClass('alert alert-danger').html('Terjadi kesalahan. Silakan coba lagi.').show();
                btn.prop('disabled', false).html('<i class="fas fa-paper-plane me-2"></i> Kirim Pesan');
            }
        });
    });
</script>
@endsection
