@extends('layouts.app')

@section('title', 'Standar Layanan Publik')

@section('content')
<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1>Standar Layanan Publik</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Standar Layanan</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="container pb-5">
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card shadow-sm border-0 sticky-top" style="top: 100px; z-index: 1;">
                <div class="list-group list-group-flush rounded-3">
                    <a href="#alur" class="list-group-item list-group-item-action active py-3" data-bs-toggle="list">
                        <i class="fas fa-project-diagram me-2 w-20"></i> Alur Layanan
                    </a>
                    <a href="#tata-cara" class="list-group-item list-group-item-action py-3" data-bs-toggle="list">
                        <i class="fas fa-clipboard-list me-2 w-20"></i> Tata Cara
                    </a>
                    <a href="#permohonan" class="list-group-item list-group-item-action py-3" data-bs-toggle="list">
                        <i class="fas fa-edit me-2 w-20"></i> Permohonan Informasi
                    </a>
                    <a href="#keberatan" class="list-group-item list-group-item-action py-3" data-bs-toggle="list">
                        <i class="fas fa-exclamation-triangle me-2 w-20"></i> Pengajuan Keberatan
                    </a>
                    <a href="#pengaduan" class="list-group-item list-group-item-action py-3" data-bs-toggle="list">
                        <i class="fas fa-bullhorn me-2 w-20"></i> Pengaduan Penyalahgunaan Wewenang
                    </a>
                    <a href="#sop" class="list-group-item list-group-item-action py-3" data-bs-toggle="list">
                        <i class="fas fa-book me-2 w-20"></i> SOP PPID
                    </a>
                    <a href="#maklumat" class="list-group-item list-group-item-action py-3" data-bs-toggle="list">
                        <i class="fas fa-scroll me-2 w-20"></i> Maklumat Pelayanan
                    </a>
                    <a href="#biaya" class="list-group-item list-group-item-action py-3" data-bs-toggle="list">
                        <i class="fas fa-coins me-2 w-20"></i> Biaya Pelayanan
                    </a>
                    <a href="#sengketa" class="list-group-item list-group-item-action py-3" data-bs-toggle="list">
                        <i class="fas fa-gavel me-2 w-20"></i> Penyelesaian Sengketa
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <div class="tab-content" style="min-height: 60vh;">
                <!-- Alur Layanan -->
                <div class="tab-pane fade show active" id="alur">
                    <div class="alur-flow-card mb-4">
                        <h3 class="fw-bold mb-1"><i class="fas fa-diagram-project me-2"></i>Mekanisme Pelayanan Informasi Publik</h3>
                        <p class="text-white-50 mb-4">Alur pengajuan permohonan informasi publik di PPID Kabupaten Empat Lawang</p>

                        <div class="alur-steps">
                            <div class="alur-step">
                                <div class="alur-num">1</div>
                                <div class="alur-box">
                                    <h6 class="fw-bold mb-2"><i class="fas fa-file-signature me-2"></i>Pemohon Mengajukan Permohonan</h6>
                                    <p class="mb-2 small">Pemohon mengisi formulir permohonan informasi dan melampirkan syarat dokumen kelengkapan:</p>
                                    <ul class="small mb-0 ps-3">
                                        <li><strong>Perorangan:</strong> KTP atau Surat Keterangan Kependudukan.</li>
                                        <li><strong>Kelompok Orang:</strong> Surat kuasa, identitas Pemberi dan Penerima Kuasa.</li>
                                        <li><strong>Badan Hukum:</strong> KTP perwakilan pengurus, surat kuasa, dan akta pendirian badan hukum yang telah disahkan Kemenkumham.</li>
                                    </ul>
                                </div>
                            </div>

                            <div class="alur-arrow"><i class="fas fa-arrow-down"></i></div>

                            <div class="alur-step">
                                <div class="alur-num">2</div>
                                <div class="alur-box">
                                    <h6 class="fw-bold mb-2"><i class="fas fa-clipboard-check me-2"></i>Verifikasi Kelengkapan Berkas</h6>
                                    <p class="mb-0 small">Petugas PPID memeriksa kelengkapan berkas permohonan yang diajukan.</p>
                                </div>
                            </div>

                            <div class="alur-arrow"><i class="fas fa-arrow-down"></i></div>

                            <div class="alur-branch">
                                <div class="alur-branch-item alur-branch-ok">
                                    <div class="alur-num">3</div>
                                    <div class="alur-box">
                                        <h6 class="fw-bold mb-1"><i class="fas fa-check-circle me-2"></i>Berkas Lengkap</h6>
                                        <p class="mb-0 small">Permohonan diproses dan dijawab paling lambat <strong>10 + 7 hari kerja</strong> (apabila informasi belum dikuasai/didokumentasikan).</p>
                                    </div>
                                </div>
                                <div class="alur-branch-item alur-branch-fail">
                                    <div class="alur-num">4</div>
                                    <div class="alur-box">
                                        <h6 class="fw-bold mb-1"><i class="fas fa-times-circle me-2"></i>Berkas Tidak Lengkap</h6>
                                        <p class="mb-0 small">PPID mengirimkan surat permohonan kelengkapan berkas kepada pemohon.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="alur-arrow"><i class="fas fa-arrow-down"></i></div>

                            <div class="alur-branch">
                                <div class="alur-branch-item alur-branch-ok">
                                    <div class="alur-num">5</div>
                                    <div class="alur-box">
                                        <h6 class="fw-bold mb-1"><i class="fas fa-thumbs-up me-2"></i>Selesai - Pemohon Puas</h6>
                                        <p class="mb-0 small">Informasi diterima dan permohonan selesai.</p>
                                    </div>
                                </div>
                                <div class="alur-branch-item alur-branch-fail">
                                    <div class="alur-num">6</div>
                                    <div class="alur-box">
                                        <h6 class="fw-bold mb-1"><i class="fas fa-gavel me-2"></i>Pemohon Tidak Puas</h6>
                                        <p class="mb-0 small">Pemohon dapat <a href="{{ route('complaint.create') }}" class="text-white text-decoration-underline">mengajukan Keberatan Informasi</a>.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card card-hover border-0 shadow-sm p-4 mb-4">
                        <h3 class="mb-4 border-bottom pb-2">Dokumen Alur Layanan</h3>
                        <div class="row">
                            @forelse($documents->where('category', 'standar_layanan_alur') as $doc)
                            <div class="col-md-6 mb-3">
                                <div class="card h-100 border bg-light">
                                    <div class="card-body">
                                        <h5 class="card-title text-primary">{{ $doc->title }}</h5>
                                        <p class="card-text text-muted small">{{ $doc->description }}</p>
                                        <a href="{{ storage_url($doc->file_path) }}" class="btn btn-sm btn-outline-primary stretched-link" target="_blank">Lihat Dokumen</a>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="col-12"><div class="alert alert-info">Belum ada dokumen alur layanan.</div></div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Tata Cara -->
                <div class="tab-pane fade" id="tata-cara">
                    <div class="card card-hover border-0 shadow-sm p-4 mb-4">
                        <h3 class="mb-4 border-bottom pb-2">Tata Cara Permohonan</h3>
                        <div class="list-group list-group-flush">
                            @forelse($documents->where('category', 'standar_layanan_tata_cara') as $doc)
                            <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                                <div>
                                    <h5 class="mb-1">{{ $doc->title }}</h5>
                                    <p class="mb-0 text-muted small">{{ $doc->description }}</p>
                                </div>
                                <a href="{{ storage_url($doc->file_path) }}" class="btn btn-sm btn-primary rounded-pill px-3" target="_blank"><i class="fas fa-download me-1"></i> Unduh</a>
                            </div>
                            @empty
                            <div class="alert alert-info">Belum ada dokumen tata cara.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Permohonan Informasi (Merged) -->
                <div class="tab-pane fade" id="permohonan">
                    <div class="card card-hover shadow-sm border-0">
                        <div class="card-header bg-white border-bottom py-3">
                            <h4 class="mb-0 text-primary fw-bold"><i class="fas fa-edit me-2"></i> Formulir Permohonan Informasi Publik</h4>
                        </div>
                        <div class="card-body p-4">
                            
                            <!-- Downloadable Forms Section -->
                            @if($documents->where('category', 'standar_layanan_permohonan')->count() > 0)
                            <div class="alert alert-light border shadow-sm mb-4">
                                <h6 class="fw-bold"><i class="fas fa-file-download me-2"></i> Unduh Formulir Offline</h6>
                                <p class="small text-muted mb-2">Jika Anda lebih memilih untuk mengajukan permohonan secara langsung/offline, silakan unduh formulir berikut:</p>
                                <div class="list-group list-group-flush">
                                    @foreach($documents->where('category', 'standar_layanan_permohonan') as $doc)
                                    <a href="{{ storage_url($doc->file_path) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-2" target="_blank">
                                        <span><i class="fas fa-file-pdf text-danger me-2"></i> {{ $doc->title }}</span>
                                        <span class="badge bg-primary rounded-pill">Unduh</span>
                                    </a>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            <div class="alert alert-info mb-4 border-start border-5 border-info">
                                <i class="fas fa-info-circle me-2"></i> Silakan lengkapi formulir di bawah ini dengan data yang benar dan valid untuk mengajukan permohonan informasi publik secara online.
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

                <!-- Pengajuan Keberatan (Merged) -->
                <div class="tab-pane fade" id="keberatan">
                    <div class="alur-flow-card alur-flow-card-danger mb-4">
                        <h3 class="fw-bold mb-1"><i class="fas fa-diagram-project me-2"></i>Mekanisme Pengajuan Keberatan Informasi</h3>
                        <p class="text-white-50 mb-4">Alur penanganan keberatan bila pemohon tidak puas dengan pelayanan informasi</p>

                        <div class="alur-steps">
                            <div class="alur-step">
                                <div class="alur-num">1</div>
                                <div class="alur-box">
                                    <h6 class="fw-bold mb-1"><i class="fas fa-file-signature me-2"></i>Pemohon Mengisi Formulir Keberatan</h6>
                                    <p class="mb-0 small">Diajukan melalui website ini (formulir di bawah) atau langsung ke kantor PPID Kabupaten Empat Lawang.</p>
                                </div>
                            </div>
                            <div class="alur-arrow"><i class="fas fa-arrow-down"></i></div>
                            <div class="alur-step">
                                <div class="alur-num">2</div>
                                <div class="alur-box">
                                    <h6 class="fw-bold mb-1"><i class="fas fa-clipboard-list me-2"></i>Registrasi & Verifikasi Berkas</h6>
                                    <p class="mb-0 small">Petugas PPID mencatat dan mengecek kelengkapan berkas keberatan.</p>
                                </div>
                            </div>
                            <div class="alur-arrow"><i class="fas fa-arrow-down"></i></div>
                            <div class="alur-step">
                                <div class="alur-num">3</div>
                                <div class="alur-box">
                                    <h6 class="fw-bold mb-1"><i class="fas fa-share me-2"></i>Diteruskan ke Atasan PPID</h6>
                                    <p class="mb-0 small">Petugas PPID menyampaikan pengajuan keberatan kepada Atasan PPID.</p>
                                </div>
                            </div>
                            <div class="alur-arrow"><i class="fas fa-arrow-down"></i></div>
                            <div class="alur-step">
                                <div class="alur-num">4</div>
                                <div class="alur-box">
                                    <h6 class="fw-bold mb-1"><i class="fas fa-reply me-2"></i>Tanggapan Keberatan</h6>
                                    <p class="mb-0 small">Atasan PPID menyampaikan tanggapan keberatan kepada pemohon paling lambat <strong>30 hari kerja</strong> sejak keberatan diregistrasi.</p>
                                </div>
                            </div>
                            <div class="alur-arrow"><i class="fas fa-arrow-down"></i></div>
                            <div class="alur-branch">
                                <div class="alur-branch-item alur-branch-ok">
                                    <div class="alur-num">5</div>
                                    <div class="alur-box">
                                        <h6 class="fw-bold mb-1"><i class="fas fa-thumbs-up me-2"></i>Selesai - Pemohon Puas</h6>
                                        <p class="mb-0 small">Keberatan dianggap terselesaikan.</p>
                                    </div>
                                </div>
                                <div class="alur-branch-item alur-branch-fail">
                                    <div class="alur-num">6</div>
                                    <div class="alur-box">
                                        <h6 class="fw-bold mb-1"><i class="fas fa-landmark me-2"></i>Pemohon Tidak Puas</h6>
                                        <p class="mb-0 small">Pemohon dapat mengajukan sengketa informasi ke <strong>Komisi Informasi Provinsi Sumatera Selatan</strong> dalam waktu <strong>14 hari kerja</strong> sejak tanggapan keberatan diterima.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card card-hover shadow-sm border-0">
                        <div class="card-header bg-white border-bottom py-3">
                            <h4 class="mb-0 text-danger fw-bold"><i class="fas fa-exclamation-triangle me-2"></i> Formulir Pengajuan Keberatan Informasi</h4>
                        </div>
                        <div class="card-body p-4">
                            
                            <!-- Downloadable Forms Section -->
                            @if($documents->where('category', 'standar_layanan_keberatan')->count() > 0)
                            <div class="alert alert-light border shadow-sm mb-4">
                                <h6 class="fw-bold"><i class="fas fa-file-download me-2"></i> Unduh Formulir Offline</h6>
                                <p class="small text-muted mb-2">Jika Anda lebih memilih untuk mengajukan keberatan secara langsung/offline, silakan unduh formulir berikut:</p>
                                <div class="list-group list-group-flush">
                                    @foreach($documents->where('category', 'standar_layanan_keberatan') as $doc)
                                    <a href="{{ storage_url($doc->file_path) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-2" target="_blank">
                                        <span><i class="fas fa-file-pdf text-danger me-2"></i> {{ $doc->title }}</span>
                                        <span class="badge bg-danger rounded-pill">Unduh</span>
                                    </a>
                                    @endforeach
                                </div>
                            </div>
                            @endif

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

                <!-- Pengaduan Penyalahgunaan Wewenang -->
                <div class="tab-pane fade" id="pengaduan">
                    <div class="alur-flow-card alur-flow-card-danger mb-4">
                        <h3 class="fw-bold mb-1"><i class="fas fa-bullhorn me-2"></i>Tata Cara Pengaduan Penyalahgunaan Wewenang</h3>
                        <p class="mb-4 small">Pengaduan atas dugaan penyalahgunaan wewenang atau pelanggaran yang dilakukan pejabat/pegawai di lingkungan Badan Publik Pemerintah Kabupaten Empat Lawang.</p>
                        <div class="alur-steps">
                            <div class="alur-step">
                                <div class="alur-num">1</div>
                                <div class="alur-box">
                                    <h6 class="fw-bold mb-1">Siapkan Data Aduan</h6>
                                    <ul class="small mb-0">
                                        <li>Identitas pengadu: nama, NIK, alamat, nomor telepon/email aktif</li>
                                        <li>Nama dan/atau jabatan pejabat yang diadukan</li>
                                        <li>Uraian kejadian: apa, siapa, kapan, di mana, bagaimana</li>
                                        <li>Bukti pendukung: dokumen, foto, tangkapan layar, rekaman, saksi</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="alur-arrow"><i class="fas fa-arrow-down"></i></div>
                            <div class="alur-step">
                                <div class="alur-num">2</div>
                                <div class="alur-box">
                                    <h6 class="fw-bold mb-1">Sampaikan Melalui Kanal Resmi</h6>
                                    <p class="small mb-0">Pilih salah satu kanal pengaduan pada tabel di bawah (SP4N-LAPOR!, Inspektorat Daerah, PPID, atau Ombudsman RI). Simpan nomor tiket/registrasi aduan sebagai bukti dan alat pantau.</p>
                                </div>
                            </div>
                            <div class="alur-arrow"><i class="fas fa-arrow-down"></i></div>
                            <div class="alur-step">
                                <div class="alur-num">3</div>
                                <div class="alur-box">
                                    <h6 class="fw-bold mb-1">Verifikasi &amp; Telaah</h6>
                                    <p class="small mb-0">Aduan diverifikasi kelengkapan dan kewenangannya paling lama <strong>3 hari kerja</strong>, lalu diteruskan kepada unit kerja terkait/Inspektorat Daerah untuk ditelaah.</p>
                                </div>
                            </div>
                            <div class="alur-arrow"><i class="fas fa-arrow-down"></i></div>
                            <div class="alur-step">
                                <div class="alur-num">4</div>
                                <div class="alur-box">
                                    <h6 class="fw-bold mb-1">Tindak Lanjut &amp; Pemeriksaan</h6>
                                    <p class="small mb-0">Unit kerja menindaklanjuti paling lama <strong>5 hari kerja</strong> sejak aduan diterima. Bila diperlukan pemeriksaan lanjutan, penyelesaian dilakukan paling lama <strong>60 hari kerja</strong> sesuai Perpres 76/2013.</p>
                                </div>
                            </div>
                            <div class="alur-arrow"><i class="fas fa-arrow-down"></i></div>
                            <div class="alur-step">
                                <div class="alur-num">5</div>
                                <div class="alur-box">
                                    <h6 class="fw-bold mb-1">Jawaban kepada Pengadu</h6>
                                    <p class="small mb-0">Hasil tindak lanjut disampaikan kepada pengadu melalui kanal yang sama paling lama <strong>10 hari kerja</strong> setelah tindak lanjut. Jika tidak puas, pengadu dapat melanjutkan aduan ke Ombudsman RI atau aparat penegak hukum.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card card-hover border-0 shadow-sm p-4 mb-4">
                        <h3 class="mb-4 border-bottom pb-2"><i class="fas fa-headset me-2"></i>Kanal Pengaduan</h3>
                        <div class="table-responsive">
                            <table class="table table-custom align-middle">
                                <thead>
                                    <tr>
                                        <th style="width: 26%">Kanal</th>
                                        <th>Alamat / Kontak</th>
                                        <th style="width: 30%">Jenis Aduan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="fw-semibold"><i class="fas fa-globe me-2 text-primary"></i>SP4N-LAPOR!</td>
                                        <td>
                                            <a href="https://www.lapor.go.id" target="_blank" rel="noopener">www.lapor.go.id</a><br>
                                            SMS ke <strong>1708</strong> (Telkomsel, Indosat, XL) &middot; Aplikasi SP4N-LAPOR! (Android/iOS) &middot; X/Twitter <a href="https://twitter.com/lapor1708" target="_blank" rel="noopener">@lapor1708</a>
                                        </td>
                                        <td class="small">Pengaduan pelayanan publik, penyalahgunaan wewenang, pungutan liar, dan dugaan korupsi</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold"><i class="fas fa-building-shield me-2 text-primary"></i>Inspektorat Daerah Kabupaten Empat Lawang</td>
                                        <td>Jl. Lintas Sumatera, Tebing Tinggi, Kabupaten Empat Lawang &mdash; datang langsung atau surat tertulis pada jam kerja</td>
                                        <td class="small">Pelanggaran disiplin dan kode etik ASN, penyalahgunaan wewenang pejabat daerah</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold"><i class="fas fa-inbox me-2 text-primary"></i>PPID Kabupaten Empat Lawang</td>
                                        <td>
                                            @foreach(($contactSettings->emails ?? ['diskominfo@empatlawangkab.go.id']) as $email)
                                                <i class="fas fa-envelope me-2 text-muted"></i>{{ is_array($email) ? ($email['email'] ?? '-') : $email }}<br>
                                            @endforeach
                                            @foreach(($contactSettings->phones ?? ['(0702) 123456']) as $phone)
                                                <i class="fas fa-phone me-2 text-muted"></i>{{ is_array($phone) ? ($phone['number'] ?? '-') : $phone }}<br>
                                            @endforeach
                                            <a href="{{ route('contact.index') }}">Formulir kontak &amp; alamat lengkap</a>
                                        </td>
                                        <td class="small">Aduan layanan informasi publik; aduan lain diteruskan ke Inspektorat Daerah</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold"><i class="fas fa-scale-balanced me-2 text-primary"></i>Ombudsman RI</td>
                                        <td>
                                            <a href="https://www.ombudsman.go.id" target="_blank" rel="noopener">www.ombudsman.go.id</a><br>
                                            Telepon <strong>137</strong> &middot; WhatsApp 0811-9553-737 &middot; Perwakilan Sumatera Selatan
                                        </td>
                                        <td class="small">Maladministrasi oleh penyelenggara negara, termasuk penyalahgunaan wewenang</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <div class="card card-hover border-0 shadow-sm h-100 p-4">
                                <h5 class="fw-bold mb-3"><i class="fas fa-user-shield me-2 text-success"></i>Perlindungan Pengadu</h5>
                                <ul class="small mb-0 ps-3">
                                    <li class="mb-2">Identitas pengadu dijaga kerahasiaannya dan hanya diketahui petugas yang menangani aduan.</li>
                                    <li class="mb-2">Pengadu berhak mendapat perlindungan sebagai pelapor/saksi sesuai UU No. 13 Tahun 2006 jo. UU No. 31 Tahun 2014 tentang Perlindungan Saksi dan Korban.</li>
                                    <li class="mb-0">Dilarang melakukan pembalasan atau tindakan intimidatif terhadap pengadu.</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card card-hover border-0 shadow-sm h-100 p-4">
                                <h5 class="fw-bold mb-3"><i class="fas fa-gavel me-2 text-primary"></i>Dasar Hukum</h5>
                                <ul class="small mb-0 ps-3">
                                    <li class="mb-2">UU No. 25 Tahun 2009 tentang Pelayanan Publik</li>
                                    <li class="mb-2">UU No. 14 Tahun 2008 tentang Keterbukaan Informasi Publik</li>
                                    <li class="mb-2">UU No. 30 Tahun 2014 tentang Administrasi Pemerintahan (larangan penyalahgunaan wewenang)</li>
                                    <li class="mb-2">Perpres No. 76 Tahun 2013 tentang Pengelolaan Pengaduan Pelayanan Publik</li>
                                    <li class="mb-0">PermenPANRB No. 62 Tahun 2018 tentang Pedoman Sistem Pengelolaan Pengaduan Pelayanan Publik Nasional (SP4N-LAPOR!)</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    @if($documents->where('category', 'standar_layanan_pengaduan')->count())
                    <div class="card card-hover border-0 shadow-sm p-4 mb-4">
                        <h3 class="mb-4 border-bottom pb-2">Dokumen Pengaduan</h3>
                        @foreach($documents->where('category', 'standar_layanan_pengaduan') as $doc)
                        <div class="d-flex align-items-start mb-3">
                            <div class="flex-shrink-0">
                                <span class="badge bg-primary rounded-circle p-3"><i class="fas fa-file-pdf fa-lg"></i></span>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5>{{ $doc->title }}</h5>
                                <p>{{ $doc->description }}</p>
                                <a href="{{ storage_url($doc->file_path) }}" class="btn btn-sm btn-outline-primary rounded-pill" target="_blank">Unduh Dokumen</a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    <div class="alert alert-warning small mb-0">
                        <i class="fas fa-triangle-exclamation me-2"></i>
                        Pengaduan yang tidak disertai identitas dan bukti pendukung yang memadai tetap dicatat, namun tindak lanjutnya terbatas. Aduan palsu atau fitnah dapat dikenakan sanksi sesuai ketentuan hukum yang berlaku.
                    </div>
                </div>

                <!-- SOP -->
                <div class="tab-pane fade" id="sop">
                    <div class="card card-hover border-0 shadow-sm p-4 mb-4">
                        <h3 class="mb-4 border-bottom pb-2">Standar Operasional Prosedur (SOP)</h3>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nama Dokumen</th>
                                        <th>Deskripsi</th>
                                        <th class="text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($documents->where('category', 'standar_layanan_sop') as $doc)
                                    <tr>
                                        <td class="fw-bold">{{ $doc->title }}</td>
                                        <td>{{ $doc->description }}</td>
                                        <td class="text-end">
                                            <a href="{{ storage_url($doc->file_path) }}" class="btn btn-sm btn-outline-success rounded-pill" target="_blank">Download</a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="3" class="text-center text-muted py-3">Belum ada data SOP.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Maklumat -->
                <div class="tab-pane fade" id="maklumat">
                    <div class="card card-hover border-0 shadow-sm p-4 mb-4">
                        <h3 class="mb-4 border-bottom pb-2 text-center">Maklumat Pelayanan Informasi Publik</h3>
                        <p class="text-center text-muted mb-4">PPID Kabupaten Empat Lawang menyatakan komitmen untuk:</p>
                        <ol class="maklumat-list mb-0">
                            <li>Memberikan pelayanan informasi yang prima berdasarkan Undang-Undang Nomor 14 Tahun 2008 tentang Keterbukaan Informasi Publik, sejalan dengan misi Pemerintah Kabupaten Empat Lawang yang berorientasi pada pelayanan publik;</li>
                            <li>Memberikan kemudahan kepada publik dalam mendapatkan informasi secara sederhana dan berbiaya ringan;</li>
                            <li>Menyediakan dan memberikan informasi publik yang dikuasai secara akurat, benar, dan tidak menyesatkan;</li>
                            <li>Memberikan jawaban permohonan informasi publik dan tanggapan pernyataan keberatan sesuai jangka waktu yang telah ditetapkan;</li>
                            <li>Menyediakan Daftar Informasi Publik untuk informasi yang wajib disediakan dan diumumkan;</li>
                            <li>Bertindak proaktif dalam memenuhi kebutuhan informasi masyarakat serta menjamin seluruh informasi publik dan fasilitas pelayanan sesuai ketentuan yang berlaku;</li>
                            <li>Menyiapkan sarana dan prasarana yang inklusif, nyaman, dan tertata baik;</li>
                            <li>Bersikap adil, tidak diskriminatif, dan berperilaku sopan santun dalam memberikan layanan informasi publik;</li>
                            <li>Tidak melakukan pungutan biaya yang tidak sesuai dengan ketentuan peraturan perundang-undangan dalam memberikan layanan informasi publik;</li>
                            <li>Melaporkan hasil kinerja atas pelaksanaan pelayanan informasi publik.</li>
                        </ol>
                    </div>

                    <div class="card card-hover border-0 shadow-sm p-4 mb-4">
                        <h3 class="mb-4 border-bottom pb-2">Dokumen Maklumat Pelayanan</h3>
                        @forelse($documents->where('category', 'standar_layanan_maklumat') as $doc)
                        <div class="text-center mb-4">
                            @php
                                $extension = pathinfo($doc->file_path, PATHINFO_EXTENSION);
                                $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                            @endphp

                            @if($isImage)
                                <img src="{{ storage_url($doc->file_path) }}" alt="{{ $doc->title }}" class="img-fluid shadow-sm rounded mb-3" style="max-height: 400px; object-fit: contain;">
                            @else
                                <div class="py-5 bg-light rounded-3 mb-3">
                                    @if(in_array(strtolower($extension), ['pdf']))
                                        <i class="fas fa-file-pdf fa-5x text-danger"></i>
                                    @elseif(in_array(strtolower($extension), ['doc', 'docx']))
                                        <i class="fas fa-file-word fa-5x text-primary"></i>
                                    @elseif(in_array(strtolower($extension), ['xls', 'xlsx']))
                                        <i class="fas fa-file-excel fa-5x text-success"></i>
                                    @else
                                        <i class="fas fa-file-alt fa-5x text-secondary"></i>
                                    @endif
                                </div>
                            @endif
                            
                            <h5>{{ $doc->title }}</h5>
                            <p class="text-muted">{{ $doc->description }}</p>
                            <a href="{{ storage_url($doc->file_path) }}" class="btn btn-primary rounded-pill mt-2" target="_blank">Unduh Maklumat</a>
                        </div>
                        @empty
                        <div class="alert alert-info">Belum ada maklumat pelayanan.</div>
                        @endforelse
                    </div>
                </div>

                <!-- Biaya -->
                <div class="tab-pane fade" id="biaya">
                    <div class="card card-hover border-0 shadow-sm p-4 mb-4">
                        <h3 class="mb-4 border-bottom pb-2">Waktu Layanan</h3>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-clock fa-lg text-primary me-3 mt-1"></i>
                                    <div>
                                        <strong>Jam Operasional</strong>
                                        <p class="text-muted small mb-0">Senin - Jumat: 08:00 - 16:00 WIB<br>Sabtu - Minggu: Tutup</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-calendar-times fa-lg text-primary me-3 mt-1"></i>
                                    <div>
                                        <strong>Hari Libur</strong>
                                        <p class="text-muted small mb-0">Layanan tutup pada hari libur nasional dan cuti bersama sesuai keputusan pemerintah pusat.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p class="small text-muted mb-0">Jam operasional dapat berubah sewaktu-waktu, cek info terkini di halaman <a href="{{ route('contact.index') }}">Kontak & Alamat</a>.</p>
                    </div>

                    <div class="card card-hover border-0 shadow-sm p-4 mb-4">
                        <h3 class="mb-4 border-bottom pb-2">Biaya Pelayanan</h3>
                         @forelse($documents->where('category', 'standar_layanan_biaya') as $doc)
                        <div class="alert alert-warning border-start border-5 border-warning">
                            <h4 class="alert-heading"><i class="fas fa-info-circle me-2"></i> {{ $doc->title }}</h4>
                            <p>{{ $doc->description }}</p>
                            <hr>
                            <a href="{{ storage_url($doc->file_path) }}" class="btn btn-sm btn-outline-dark" target="_blank">Lihat Rincian Biaya</a>
                        </div>
                        @empty
                         <div class="alert alert-success border-start border-5 border-success">
                            <h4 class="alert-heading"><i class="fas fa-check-circle me-2"></i> Gratis!</h4>
                            <p>Layanan informasi publik di Kabupaten Empat Lawang tidak dipungut biaya (GRATIS), kecuali untuk biaya penggandaan atau perekaman dokumen yang timbul sesuai dengan peraturan perundang-undangan yang berlaku.</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                <!-- Sengketa -->
                <div class="tab-pane fade" id="sengketa">
                    <div class="card card-hover border-0 shadow-sm p-4 mb-4">
                        <h3 class="mb-4 border-bottom pb-2">Penyelesaian Sengketa</h3>
                        @forelse($documents->where('category', 'standar_layanan_sengketa') as $doc)
                        <div class="d-flex align-items-start mb-3">
                            <div class="flex-shrink-0">
                                <span class="badge bg-primary rounded-circle p-3"><i class="fas fa-balance-scale fa-lg"></i></span>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5>{{ $doc->title }}</h5>
                                <p>{{ $doc->description }}</p>
                                <a href="{{ storage_url($doc->file_path) }}" class="btn btn-sm btn-outline-primary rounded-pill" target="_blank">Pelajari Prosedur</a>
                            </div>
                        </div>
                        @empty
                        <div class="alert alert-info">Belum ada informasi penyelesaian sengketa.</div>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-success text-white border-bottom-0">
                <h5 class="modal-title fw-bold" id="successModalTitle">Berhasil</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <div class="mb-3 text-success">
                    <i class="fas fa-check-circle fa-4x"></i>
                </div>
                <h5 class="fw-bold mb-3" id="successModalTitleBody">Pengajuan Berhasil!</h5>
                <p class="text-muted" id="successModalBody">
                    Permohonan Anda telah kami terima.
                </p>
                <button type="button" class="btn btn-success rounded-pill px-4 mt-3" data-bs-dismiss="modal">Mengerti</button>
            </div>
        </div>
    </div>
</div>

<style>
    .w-20 { width: 25px; text-align: center; }
    .list-group-item.active {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }

    /* Alur Mekanisme Pelayanan - numbered step infographic */
    .alur-flow-card {
        background: linear-gradient(135deg, var(--primary-color) 0%, #012a4a 100%);
        border-radius: 1.5rem;
        padding: 2rem;
        color: #fff;
    }
    .alur-flow-card-danger {
        background: linear-gradient(135deg, #b91c1c 0%, #450a0a 100%);
    }
    .alur-flow-card h3,
    .alur-flow-card h6,
    .alur-flow-card p,
    .alur-flow-card li {
        color: #fff;
    }
    .alur-box a { color: #fff; }
    .maklumat-list { padding-left: 1.5rem; }
    .maklumat-list li { margin-bottom: 0.75rem; line-height: 1.6; }
    .alur-steps { display: flex; flex-direction: column; align-items: stretch; gap: 0; }
    .alur-step { display: flex; align-items: flex-start; gap: 1rem; }
    .alur-num {
        flex-shrink: 0;
        width: 42px; height: 42px;
        border-radius: 50%;
        background: var(--secondary-color);
        color: #012a4a;
        font-weight: 800;
        font-size: 1.1rem;
        display: flex; align-items: center; justify-content: center;
    }
    .alur-box {
        background: rgba(255,255,255,0.12);
        border: 1px solid rgba(255,255,255,0.2);
        border-radius: 1rem;
        padding: 1rem 1.25rem;
        flex: 1;
    }
    .alur-box ul { color: rgba(255,255,255,0.9); }
    .alur-arrow {
        text-align: center;
        color: var(--secondary-color);
        font-size: 1.25rem;
        padding: 0.5rem 0 0.5rem 21px;
    }
    .alur-branch { display: flex; gap: 1rem; flex-wrap: wrap; }
    .alur-branch-item { display: flex; align-items: flex-start; gap: 1rem; flex: 1 1 260px; }
    .alur-branch-ok .alur-num { background: #22c55e; color: #fff; }
    .alur-branch-fail .alur-num { background: #ef4444; color: #fff; }
    .alur-branch-ok .alur-box { border-color: rgba(34,197,94,0.5); }
    .alur-branch-fail .alur-box { border-color: rgba(239,68,68,0.5); }

    @media (max-width: 767px) {
        .alur-branch { flex-direction: column; }
    }
</style>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Check for session success message and show modal
        @if(session('success_modal_message'))
            var successModal = new bootstrap.Modal(document.getElementById('successModal'));
            document.getElementById('successModalTitle').innerText = "{{ session('success_modal_title', 'Berhasil') }}";
            document.getElementById('successModalTitleBody').innerText = "{{ session('success_modal_title', 'Berhasil') }}";
            document.getElementById('successModalBody').innerHTML = "{!! session('success_modal_message') !!}";
            successModal.show();
        @endif

        // Auto-open tab if validation errors exist
        @if($errors->any())
            @if($errors->has('info_requested') || $errors->has('nik') || $errors->has('address') || $errors->has('ktp_file'))
                var triggerEl = document.querySelector('.list-group-item[href="#permohonan"]');
                if(triggerEl) {
                    var tab = new bootstrap.Tab(triggerEl);
                    tab.show();
                }
            @elseif($errors->has('reason_complaint'))
                var triggerEl = document.querySelector('.list-group-item[href="#keberatan"]');
                if(triggerEl) {
                    var tab = new bootstrap.Tab(triggerEl);
                    tab.show();
                }
            @endif
        @endif

        // Activate tab based on hash
        var hash = window.location.hash;
        if (hash) {
            var triggerEl = document.querySelector('.list-group-item[href="' + hash + '"]');
            if (triggerEl) {
                var tab = new bootstrap.Tab(triggerEl);
                tab.show();
            }
        }

        // Update hash when tab changes
        var tabElList = [].slice.call(document.querySelectorAll('a[data-bs-toggle="list"]'))
        tabElList.forEach(function (tabEl) {
            tabEl.addEventListener('shown.bs.tab', function (event) {
                var href = event.target.getAttribute('href');
                if(history.pushState) {
                    history.pushState(null, null, href);
                } else {
                    window.location.hash = href;
                }
            })
        })
    });
</script>
@endpush
