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
                    <div class="card card-hover border-0 shadow-sm p-4 mb-4">
                        <h3 class="mb-4 border-bottom pb-2">Alur Layanan Informasi</h3>
                        <div class="row">
                            @forelse($documents->where('category', 'standar_layanan_alur') as $doc)
                            <div class="col-md-6 mb-3">
                                <div class="card h-100 border bg-light">
                                    <div class="card-body">
                                        <h5 class="card-title text-primary">{{ $doc->title }}</h5>
                                        <p class="card-text text-muted small">{{ $doc->description }}</p>
                                        <a href="{{ asset('storage/' . $doc->file_path) }}" class="btn btn-sm btn-outline-primary stretched-link" target="_blank">Lihat Dokumen</a>
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
                                <a href="{{ asset('storage/' . $doc->file_path) }}" class="btn btn-sm btn-primary rounded-pill px-3" target="_blank"><i class="fas fa-download me-1"></i> Unduh</a>
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
                                    <a href="{{ asset('storage/' . $doc->file_path) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-2" target="_blank">
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
                                    <a href="{{ asset('storage/' . $doc->file_path) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-2" target="_blank">
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
                                            <a href="{{ asset('storage/' . $doc->file_path) }}" class="btn btn-sm btn-outline-success rounded-pill" target="_blank">Download</a>
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
                        <h3 class="mb-4 border-bottom pb-2">Maklumat Pelayanan</h3>
                        @forelse($documents->where('category', 'standar_layanan_maklumat') as $doc)
                        <div class="text-center mb-4">
                            @php
                                $extension = pathinfo($doc->file_path, PATHINFO_EXTENSION);
                                $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                            @endphp

                            @if($isImage)
                                <img src="{{ asset('storage/' . $doc->file_path) }}" alt="{{ $doc->title }}" class="img-fluid shadow-sm rounded mb-3" style="max-height: 400px; object-fit: contain;">
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
                            <a href="{{ asset('storage/' . $doc->file_path) }}" class="btn btn-primary rounded-pill mt-2" target="_blank">Unduh Maklumat</a>
                        </div>
                        @empty
                        <div class="alert alert-info">Belum ada maklumat pelayanan.</div>
                        @endforelse
                    </div>
                </div>

                <!-- Biaya -->
                <div class="tab-pane fade" id="biaya">
                    <div class="card card-hover border-0 shadow-sm p-4 mb-4">
                        <h3 class="mb-4 border-bottom pb-2">Biaya Pelayanan</h3>
                         @forelse($documents->where('category', 'standar_layanan_biaya') as $doc)
                        <div class="alert alert-warning border-start border-5 border-warning">
                            <h4 class="alert-heading"><i class="fas fa-info-circle me-2"></i> {{ $doc->title }}</h4>
                            <p>{{ $doc->description }}</p>
                            <hr>
                            <a href="{{ asset('storage/' . $doc->file_path) }}" class="btn btn-sm btn-outline-dark" target="_blank">Lihat Rincian Biaya</a>
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
                                <a href="{{ asset('storage/' . $doc->file_path) }}" class="btn btn-sm btn-outline-primary rounded-pill" target="_blank">Pelajari Prosedur</a>
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
