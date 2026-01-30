@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-6">
        <h2 class="mb-4">Kontak Kami</h2>
        <div class="card mb-4">
            <div class="card-body">
                <h5><i class="fas fa-map-marker-alt text-primary me-2"></i> Alamat</h5>
                <p>Jl. Poros No. 1, Tebing Tinggi, Kabupaten Empat Lawang, Sumatera Selatan.</p>

                <h5><i class="fas fa-envelope text-primary me-2"></i> Email</h5>
                <p>ppid@empatlawangkab.go.id</p>

                <h5><i class="fas fa-phone text-primary me-2"></i> Telepon</h5>
                <p>(0702) 123456</p>
            </div>
        </div>
        
        <div class="ratio ratio-16x9">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3981.6558661642846!2d103.07663431475878!3d-3.665679997332367!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e308f9f6e7f7b1d%3A0x6e7f7b1d6e7f7b1d!2sKantor%20Bupati%20Empat%20Lawang!5e0!3m2!1sid!2sid!4v1625637283728!5m2!1sid!2sid" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
        </div>
    </div>

    <div class="col-md-6">
        <h2 class="mb-4">Kirim Pesan</h2>
        <div class="card">
            <div class="card-body">
                <form action="{{ route('contact.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nomor Telepon</label>
                        <input type="text" name="phone" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subjek</label>
                        <input type="text" name="subject" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pesan</label>
                        <textarea name="message" class="form-control" rows="5" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Kirim Pesan</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
