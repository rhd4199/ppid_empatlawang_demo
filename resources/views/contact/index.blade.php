@extends('layouts.app')

@section('title', 'Hubungi Kami')

@section('content')
<!-- Modern Page Header with Animated Background -->
<div class="page-header position-relative overflow-hidden">
    <!-- Animated Shapes Background -->
    <div class="position-absolute top-0 start-0 w-100 h-100 overflow-hidden" style="z-index: 0;">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
    </div>
    
    <div class="container position-relative" style="z-index: 1;">
        <div class="row justify-content-center">
            <div class="col-md-8 text-center">
                <span class="badge bg-white bg-opacity-25 text-white border border-white border-opacity-25 rounded-pill px-3 py-2 mb-3 backdrop-blur">
                    <i class="fas fa-headset me-2"></i> Layanan Pengaduan & Informasi
                </span>
                <h1 class="display-3 fw-bold mb-3 text-white">Hubungi Kami</h1>
                <p class="lead text-white text-opacity-75 mb-4">Saluran komunikasi resmi Pemerintah Kabupaten Empat Lawang. Kami siap melayani Anda.</p>
                <nav aria-label="breadcrumb" class="d-flex justify-content-center">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-white text-opacity-75 text-decoration-none">Beranda</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">Kontak</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="container pb-5 mt-n5 position-relative" style="z-index: 2;">
    <!-- Contact Info Cards (Floating) -->
    <div class="row g-4 mb-5">
        <!-- Address -->
        <div class="col-md-3 col-sm-6">
            <div class="card h-100 border-0 shadow-lg hover-lift card-glass overflow-hidden">
                <div class="card-body text-center p-4">
                    <div class="icon-circle bg-primary-gradient text-white mb-3 mx-auto shadow-sm">
                        <i class="fas fa-map-marker-alt fa-lg"></i>
                    </div>
                    <h6 class="fw-bold mb-1">Alamat Kantor</h6>
                    <p class="text-muted small mb-0">{{ $settings->address ?? 'Jl. Lintas Sumatera No. 1, Tebing Tinggi, Empat Lawang' }}</p>
                </div>
                <div class="card-footer bg-transparent border-0 pb-3 pt-0">
                    <a href="#map-section" class="btn btn-sm btn-outline-primary rounded-pill w-100">Lihat Peta</a>
                </div>
            </div>
        </div>
        <!-- Phone -->
        <div class="col-md-3 col-sm-6">
            <div class="card h-100 border-0 shadow-lg hover-lift card-glass overflow-hidden">
                <div class="card-body text-center p-4">
                    <div class="icon-circle bg-warning-gradient text-white mb-3 mx-auto shadow-sm">
                        <i class="fas fa-phone-alt fa-lg"></i>
                    </div>
                    <h6 class="fw-bold mb-1">Telepon & WA</h6>
                    @if(!empty($settings->phones) && count($settings->phones) > 0)
                        @foreach($settings->phones as $phone)
                            <p class="text-muted small mb-0">{{ $phone }}</p>
                        @endforeach
                    @else
                        <p class="text-muted small mb-0">(0702) 123456</p>
                        <p class="text-muted small mb-0">+62 812-3456-7890</p>
                    @endif
                </div>
                <div class="card-footer bg-transparent border-0 pb-3 pt-0">
                    <a href="tel:{{ !empty($settings->phones) ? preg_replace('/[^0-9]/', '', $settings->phones[0]) : '0702123456' }}" class="btn btn-sm btn-outline-warning rounded-pill w-100">Hubungi Sekarang</a>
                </div>
            </div>
        </div>
        <!-- Email -->
        <div class="col-md-3 col-sm-6">
            <div class="card h-100 border-0 shadow-lg hover-lift card-glass overflow-hidden">
                <div class="card-body text-center p-4">
                    <div class="icon-circle bg-success-gradient text-white mb-3 mx-auto shadow-sm">
                        <i class="fas fa-envelope fa-lg"></i>
                    </div>
                    <h6 class="fw-bold mb-1">Email Resmi</h6>
                    @if(!empty($settings->emails) && count($settings->emails) > 0)
                        @foreach($settings->emails as $email)
                            <p class="text-muted small mb-0">{{ $email }}</p>
                        @endforeach
                    @else
                        <p class="text-muted small mb-0">diskominfo@empatlawangkab.go.id</p>
                        <p class="text-muted small mb-0">ppid@empatlawangkab.go.id</p>
                    @endif
                </div>
                <div class="card-footer bg-transparent border-0 pb-3 pt-0">
                    <a href="mailto:{{ !empty($settings->emails) ? $settings->emails[0] : 'diskominfo@empatlawangkab.go.id' }}" class="btn btn-sm btn-outline-success rounded-pill w-100">Kirim Email</a>
                </div>
            </div>
        </div>
        <!-- Hours -->
        <div class="col-md-3 col-sm-6">
            <div class="card h-100 border-0 shadow-lg hover-lift card-glass overflow-hidden">
                <div class="card-body text-center p-4">
                    <div class="icon-circle bg-info-gradient text-white mb-3 mx-auto shadow-sm">
                        <i class="fas fa-clock fa-lg"></i>
                    </div>
                    <h6 class="fw-bold mb-1">Jam Layanan</h6>
                    @if(!empty($settings->working_hours) && count($settings->working_hours) > 0)
                        @foreach($settings->working_hours as $hours)
                            <p class="text-muted small mb-0">{{ $hours }}</p>
                        @endforeach
                    @else
                        <p class="text-muted small mb-0">Senin - Jumat: 08:00 - 16:00</p>
                        <p class="text-muted small mb-0">Sabtu - Minggu: Tutup</p>
                    @endif
                </div>
                <center>
                    <div class="card-footer bg-transparent border-0 pb-3 pt-0 ">
                        <span class="badge bg-success-subtle text-success rounded-pill ">Buka Sekarang</span>
                    </div>
                </center>
            </div>
        </div>
    </div>

    <div class="row g-5">
        @php
            $socials = $settings->social_media ?? [];
            if(!is_array($socials)) $socials = [];
            $instagrams = array_filter($socials, fn($s) => ($s['platform'] ?? '') === 'instagram');
            $facebooks = array_filter($socials, fn($s) => ($s['platform'] ?? '') === 'facebook');
            $others = array_filter($socials, fn($s) => !in_array(($s['platform'] ?? ''), ['instagram', 'facebook']));
        @endphp
        <!-- Left Column: Social Media Ecosystem -->
        <div class="col-lg-5">
            <div class="mb-5">
                <div class="d-flex align-items-center mb-4">
                    <h3 class="fw-bold mb-0 me-3">Jejaring Sosial</h3>
                    <div class="flex-grow-1 border-bottom"></div>
                </div>
                <p class="text-muted mb-4">Terhubung dengan berbagai akun resmi Pemerintah Kabupaten Empat Lawang untuk update informasi terkini.</p>
                
                <!-- Instagram Ecosystem -->
                <div class="social-card mb-4">
                    <div class="social-header instagram-gradient text-white p-3 rounded-top-4 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <i class="fab fa-instagram fa-2x me-3"></i>
                            <div>
                                <h6 class="mb-0 fw-bold text-white"><b>Instagram</b></h6>
                            </div>
                        </div>
                        <i class="fas fa-arrow-right opacity-50"></i>
                    </div>
                    <div class="bg-white border border-top-0 rounded-bottom-4 shadow-sm overflow-hidden">
                        <div class="list-group list-group-flush">
                            @forelse($instagrams as $ig)
                            <a href="{{ $ig['url'] ?? '#' }}" target="_blank" class="list-group-item list-group-item-action p-3 d-flex align-items-center border-0 border-bottom social-item">
                                <div class="avatar-sm rounded-circle bg-light d-flex align-items-center justify-content-center me-3 {{ $ig['color'] ?? 'text-danger' }} fw-bold border">
                                    <i class="{{ $ig['icon'] ?? 'fab fa-instagram' }}"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0 fw-bold text-dark">{{ $ig['name'] }}</h6>
                                    <small class="text-muted">{{ $ig['username'] ?? '' }}</small>
                                </div>
                                <span class="btn btn-sm btn-light rounded-pill px-3 fw-bold {{ $ig['color'] ?? 'text-danger' }}">Follow</span>
                            </a>
                            @empty
                            <!-- Akun 1 -->
                            <a href="#" class="list-group-item list-group-item-action p-3 d-flex align-items-center border-0 border-bottom social-item">
                                <div class="avatar-sm rounded-circle bg-light d-flex align-items-center justify-content-center me-3 text-danger fw-bold border">
                                    PK
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0 fw-bold text-dark">Pemkab Empat Lawang</h6>
                                    <small class="text-muted">@empatlawang_kab</small>
                                </div>
                                <span class="btn btn-sm btn-light rounded-pill px-3 fw-bold text-danger">Follow</span>
                            </a>
                            <!-- Akun 2 -->
                            <a href="#" class="list-group-item list-group-item-action p-3 d-flex align-items-center border-0 border-bottom social-item">
                                <div class="avatar-sm rounded-circle bg-light d-flex align-items-center justify-content-center me-3 text-primary fw-bold border">
                                    KO
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0 fw-bold text-dark">Diskominfo</h6>
                                    <small class="text-muted">@kominfo_empatlawang</small>
                                </div>
                                <span class="btn btn-sm btn-light rounded-pill px-3 fw-bold text-primary">Follow</span>
                            </a>
                            <!-- Akun 3 -->
                            <a href="#" class="list-group-item list-group-item-action p-3 d-flex align-items-center border-0 social-item">
                                <div class="avatar-sm rounded-circle bg-light d-flex align-items-center justify-content-center me-3 text-success fw-bold border">
                                    PP
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0 fw-bold text-dark">PPID Utama</h6>
                                    <small class="text-muted">@ppid_empatlawang</small>
                                </div>
                                <span class="btn btn-sm btn-light rounded-pill px-3 fw-bold text-success">Follow</span>
                            </a>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Facebook Ecosystem -->
                <div class="social-card mb-4">
                    <div class="social-header facebook-gradient text-white p-3 rounded-top-4 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <i class="fab fa-facebook fa-2x me-3"></i>
                            <div>
                                <h6 class="mb-0 fw-bold text-white"><b>Facebook Page</b></h6>
                            </div>
                        </div>
                        <i class="fas fa-arrow-right opacity-50"></i>
                    </div>
                    <div class="bg-white border border-top-0 rounded-bottom-4 shadow-sm overflow-hidden">
                        <div class="list-group list-group-flush">
                            @forelse($facebooks as $fb)
                            <a href="{{ $fb['url'] ?? '#' }}" target="_blank" class="list-group-item list-group-item-action p-3 d-flex align-items-center border-0 social-item">
                                <div class="avatar-sm rounded-circle bg-light d-flex align-items-center justify-content-center me-3 {{ $fb['color'] ?? 'text-primary' }} fw-bold border">
                                    <i class="{{ $fb['icon'] ?? 'fab fa-facebook-f' }}"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0 fw-bold text-dark">{{ $fb['name'] }}</h6>
                                    <small class="text-muted">{{ $fb['username'] ?? '' }}</small>
                                </div>
                                <span class="btn btn-sm btn-light rounded-pill px-3 fw-bold {{ $fb['color'] ?? 'text-primary' }}">Like</span>
                            </a>
                            @empty
                            <a href="#" class="list-group-item list-group-item-action p-3 d-flex align-items-center border-0 social-item">
                                <div class="avatar-sm rounded-circle bg-light d-flex align-items-center justify-content-center me-3 text-primary fw-bold border">
                                    <i class="fas fa-building"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0 fw-bold text-dark">Pemerintah Kab. Empat Lawang</h6>
                                    <small class="text-muted">Official Page • 15K Followers</small>
                                </div>
                                <span class="btn btn-sm btn-light rounded-pill px-3 fw-bold text-primary">Like</span>
                            </a>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Other Channels Grid -->
                <div class="row g-3">
                    @forelse($others as $other)
                    @if ($other['platform'] == 'youtube')
                        <div class="col-6">
                            <a href="{{ $other['url'] ?? '#' }}" target="_blank" class="card h-100 border-0 shadow-sm hover-scale text-decoration-none overflow-hidden">
                                <div class="card-body p-3 d-flex align-items-center">
                                    <div class="icon-sm bg-danger text-white rounded-circle me-3 d-flex align-items-center justify-content-center" style="background-color: red !important">
                                        <i class="fab fa-youtube"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold text-dark small">{{ $other['name'] }}</h6>
                                        <small class="text-muted x-small">{{ $other['username'] ?? '' }}</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @elseif ($other['platform'] == 'twitter')
                        <div class="col-6">
                            <a href="{{ $other['url'] ?? '#' }}" target="_blank" class="card h-100 border-0 shadow-sm hover-scale text-decoration-none overflow-hidden">
                                <div class="card-body p-3 d-flex align-items-center">
                                    <div class="icon-sm bg-dark text-white rounded-circle me-3 d-flex align-items-center justify-content-center">
                                        <i class="fab fa-twitter"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold text-dark small">{{ $other['name'] }}</h6>
                                        <small class="text-muted x-small">{{ $other['username'] ?? '' }}</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @else
                        <div class="col-6">
                            <a href="{{ $other['url'] ?? '#' }}" target="_blank" class="card h-100 border-0 shadow-sm hover-scale text-decoration-none overflow-hidden">
                                <div class="card-body p-3 d-flex align-items-center">
                                    <div class="icon-sm {{ ($other['color'] ?? false) ? str_replace('text-', 'bg-', $other['color']) : 'bg-dark' }} text-white rounded-circle me-3 d-flex align-items-center justify-content-center">
                                        <i class="{{ $other['icon'] ?? 'fas fa-link' }}"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold text-dark small">{{ $other['name'] }}</h6>
                                        <small class="text-muted x-small">{{ $other['username'] ?? '' }}</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endif
                    @empty
                    <div class="col-6">
                        <a href="#" class="card h-100 border-0 shadow-sm hover-scale text-decoration-none overflow-hidden">
                            <div class="card-body p-3 d-flex align-items-center">
                                <div class="icon-sm bg-danger text-white rounded-circle me-3 d-flex align-items-center justify-content-center" style="background-color: red !important">
                                    <i class="fab fa-youtube"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold text-dark small">Youtube</h6>
                                    <small class="text-muted x-small">Empat Lawang TV</small>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="#" class="card h-100 border-0 shadow-sm hover-scale text-decoration-none overflow-hidden">
                            <div class="card-body p-3 d-flex align-items-center">
                                <div class="icon-sm bg-dark text-white rounded-circle me-3 d-flex align-items-center justify-content-center">
                                    <i class="fab fa-twitter"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold text-dark small">Twitter / X</h6>
                                    <small class="text-muted x-small">@pemkab_4L</small>
                                </div>
                            </div>
                        </a>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Map -->
            <div id="map-section" class="card border-0 shadow-lg overflow-hidden rounded-4">
                <div class="position-relative">
                    <iframe src="{{ $settings->maps_embed ?? 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3983.5!2d103.0!3d-3.7!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zM8KwNDInMDAuMCJTIDEwM8KwMDAnMDAuMCJF!5e0!3m2!1sen!2sid!4v1620000000000!5m2!1sen!2sid' }}" width="100%" height="300" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                    <div class="position-absolute bottom-0 start-0 m-3">
                        <a href="https://maps.google.com" target="_blank" class="btn btn-sm btn-light shadow-sm rounded-pill fw-bold">
                            <i class="fas fa-external-link-alt me-1"></i> Buka di Google Maps
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Feedback Form -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden position-sticky" style="top: 100px;">
                <div class="card-header bg-primary text-white p-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-1 fw-bold text-white"><i class="fas fa-paper-plane me-2"></i> Kirim Pesan</h3>
                    </div>
                    <div class="bg-white bg-opacity-10 p-2 rounded-circle">
                        <i class="fas fa-comments fa-2x text-white"></i>
                    </div>
                </div>
                <div class="card-body p-4 p-md-5 bg-white">
                    <form action="{{ route('contact.store') }}" method="POST" id="contact-form">
                        @csrf
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label for="name" class="form-label fw-bold text-black text-uppercase x-small ls-1">Nama Lengkap</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control bg-light border-start-0 ps-0" id="name" name="name" placeholder="Nama Anda" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label fw-bold text-black text-uppercase x-small ls-1">Alamat Email</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control bg-light border-start-0 ps-0" id="email" name="email" placeholder="email@contoh.com" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label fw-bold text-black text-uppercase x-small ls-1">Nomor Telepon / WA</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-phone"></i></span>
                                    <input type="text" class="form-control bg-light border-start-0 ps-0" id="phone" name="phone" placeholder="08xxx">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="subject" class="form-label fw-bold text-black text-uppercase x-small ls-1">Subjek Pesan</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-tag"></i></span>
                                    <input type="text" class="form-control bg-light border-start-0 ps-0" id="subject" name="subject" placeholder="Topik..." required>
                                </div>
                            </div>
                            <div class="col-12">
                                <label for="message" class="form-label fw-bold text-black text-uppercase x-small ls-1">Isi Pesan</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted pt-3 align-items-start"><i class="fas fa-pen"></i></span>
                                    <textarea class="form-control bg-light border-start-0 ps-0" id="message" name="message" rows="5" placeholder="Tuliskan pesan Anda secara detail..." required></textarea>
                                </div>
                            </div>
                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary w-100 py-3 fw-bold rounded-pill shadow-lg hover-scale transition-all d-flex align-items-center justify-content-center" id="submit-btn">
                                    <span>Kirim Pesan Sekarang</span>
                                    <i class="fas fa-paper-plane ms-2"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                    
                    <!-- Styled Alert Message -->
                    <div id="form-message" class="alert mt-4 d-none border-0 shadow-sm" role="alert">
                        <div class="d-flex">
                            <div class="me-3">
                                <div class="icon-status rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="fas fa-check"></i>
                                </div>
                            </div>
                            <div>
                                <h5 class="alert-heading fw-bold mb-1 fs-6">Status Pengiriman</h5>
                                <p class="mb-0 message-content small text-muted">Pesan Anda telah terkirim.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Gradient Backgrounds */
    .bg-primary-gradient { background: linear-gradient(135deg, #0d6efd 0%, #0043a8 100%); }
    .bg-success-gradient { background: linear-gradient(135deg, #198754 0%, #0a5c36 100%); }
    .bg-info-gradient { background: linear-gradient(135deg, #0dcaf0 0%, #059dc0 100%); }
    .bg-warning-gradient { background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%); }
    
    .instagram-gradient { background: linear-gradient(45deg, #405de6, #5851db, #833ab4, #c13584, #e1306c, #fd1d1d); }
    .facebook-gradient { background: linear-gradient(to right, #1877f2, #0056b3); }
    
    /* Utilities */
    .ls-1 { letter-spacing: 1px; }
    .x-small { font-size: 0.75rem; }
    .mt-n5 { margin-top: -5rem; }
    
    /* Shapes for Hero */
    .shape { position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.6; }
    .shape-1 { background: #004d40; width: 400px; height: 400px; top: -100px; right: -100px; animation: float 10s infinite alternate; }
    .shape-2 { background: #ffc107; width: 300px; height: 300px; bottom: -50px; left: -100px; animation: float 8s infinite alternate-reverse; }
    .shape-3 { background: #00695c; width: 200px; height: 200px; top: 40%; left: 40%; animation: float 12s infinite alternate; }
    
    @keyframes float {
        0% { transform: translate(0, 0); }
        100% { transform: translate(30px, 50px); }
    }
    
    /* Glassmorphism */
    .backdrop-blur { backdrop-filter: blur(10px); }
    .card-glass {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.5);
    }
    
    /* Icons */
    .icon-circle {
        width: 60px; height: 60px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
    }
    .icon-sm { width: 40px; height: 40px; }
    .avatar-sm { width: 40px; height: 40px; font-size: 0.8rem; }
    
    /* Hover Effects */
    .hover-lift { transition: transform 0.3s ease, box-shadow 0.3s ease; }
    .hover-lift:hover { transform: translateY(-10px); box-shadow: 0 1rem 3rem rgba(0,0,0,.175)!important; }
    
    .hover-scale { transition: transform 0.2s ease; }
    .hover-scale:hover { transform: scale(1.02); }
    
    .social-item { transition: background-color 0.2s; }
    .social-item:hover { background-color: #f8f9fa; }
    
    /* Form Inputs */
    .form-control:focus, .input-group-text { border-color: #e9ecef; }
    .form-control:focus { box-shadow: none; background-color: #fff !important; }
    .input-group:focus-within { box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.15); border-radius: 0.5rem; }
    .input-group:focus-within .form-control, .input-group:focus-within .input-group-text { border-color: var(--bs-primary); background-color: #fff; }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $('#contact-form').submit(function(e) {
            e.preventDefault();
            let btn = $('#submit-btn');
            let originalContent = btn.html();
            
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Mengirim...');
            
            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    $('#form-message').removeClass('d-none alert-danger bg-danger-subtle').addClass('alert-success bg-success-subtle');
                    $('#form-message .alert-heading').text('Pesan Terkirim!');
                    $('#form-message .message-content').text(response.message);
                    $('#form-message .icon-status').removeClass('bg-danger text-white').addClass('bg-success text-white').html('<i class="fas fa-check"></i>');
                    
                    $('#contact-form')[0].reset();
                    btn.prop('disabled', false).html(originalContent);
                    
                    $('html, body').animate({ scrollTop: $("#form-message").offset().top - 150 }, 500);
                },
                error: function(xhr) {
                    let errorMsg = 'Terjadi kesalahan. Silakan coba lagi.';
                    if(xhr.responseJSON && xhr.responseJSON.message) errorMsg = xhr.responseJSON.message;
                    
                    $('#form-message').removeClass('d-none alert-success bg-success-subtle').addClass('alert-danger bg-danger-subtle');
                    $('#form-message .alert-heading').text('Gagal Mengirim');
                    $('#form-message .message-content').text(errorMsg);
                    $('#form-message .icon-status').removeClass('bg-success text-white').addClass('bg-danger text-white').html('<i class="fas fa-times"></i>');
                    
                    btn.prop('disabled', false).html(originalContent);
                }
            });
        });
    });
</script>
@endsection
