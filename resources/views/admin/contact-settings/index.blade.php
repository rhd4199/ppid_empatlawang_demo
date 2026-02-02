@extends('layouts.admin')

@section('title', 'Pengaturan Kontak')

@section('content')
<div class="container-fluid pb-5">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800 fw-bold">Pengaturan Kontak & Profil</h1>
            <p class="text-muted small mb-0">Kelola informasi kontak, peta lokasi, dan jejaring sosial resmi.</p>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger border-0 shadow-sm alert-dismissible fade show" role="alert">
        <ul class="mb-0 small">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <form action="{{ route('admin.contact-settings.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <!-- Section 1: Identity & Location -->
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header bg-white py-3 px-4 border-bottom">
                        <h6 class="m-0 fw-bold text-primary"><i class="fas fa-map-marker-alt me-2"></i> Identitas & Lokasi Kantor</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-lg-12">
                                <div class="mb-4">
                                    <label class="form-label fw-semibold text-black small text-uppercase ls-1">Alamat Lengkap</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-building text-muted"></i></span>
                                        <textarea class="form-control border-start-0 ps-0 bg-light" name="address" rows="3" placeholder="Masukkan alamat lengkap kantor...">{{ old('address', $settings->address) }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 2: Communication Channels -->
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header bg-white py-3 px-4 border-bottom">
                        <h6 class="m-0 fw-bold text-info"><i class="fas fa-headset me-2"></i> Saluran Komunikasi</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <!-- Phones -->
                            <div class="col-md-4">
                                <div class="p-3 bg-light rounded-3 h-100 border border-light">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <label class="form-label fw-bold text-dark mb-0"><i class="fas fa-phone-alt me-2 text-warning"></i> Telepon</label>
                                        <button type="button" class="btn btn-sm btn-light shadow-sm text-primary rounded-circle" onclick="addItem('phones-container', 'phones[]', 'fa-phone')" title="Tambah"><i class="fas fa-plus"></i></button>
                                    </div>
                                    <div id="phones-container" class="vstack gap-2">
                                        @forelse(old('phones', $settings->phones ?? []) as $phone)
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-white"><i class="fas fa-phone fa-xs text-muted"></i></span>
                                            <input type="text" class="form-control bg-white" name="phones[]" value="{{ $phone }}" placeholder="Contoh: (021) 1234567">
                                            <button class="btn btn-white text-danger border" type="button" onclick="this.closest('.input-group').remove()"><i class="fas fa-times"></i></button>
                                        </div>
                                        @empty
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-white"><i class="fas fa-phone fa-xs text-muted"></i></span>
                                            <input type="text" class="form-control bg-white" name="phones[]" placeholder="Contoh: (021) 1234567">
                                            <button class="btn btn-white text-danger border" type="button" onclick="this.closest('.input-group').remove()"><i class="fas fa-times"></i></button>
                                        </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>

                            <!-- Emails -->
                            <div class="col-md-4">
                                <div class="p-3 bg-light rounded-3 h-100 border border-light">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <label class="form-label fw-bold text-dark mb-0"><i class="fas fa-envelope me-2 text-success"></i> Email</label>
                                        <button type="button" class="btn btn-sm btn-light shadow-sm text-primary rounded-circle" onclick="addItem('emails-container', 'emails[]', 'fa-envelope')" title="Tambah"><i class="fas fa-plus"></i></button>
                                    </div>
                                    <div id="emails-container" class="vstack gap-2">
                                        @forelse(old('emails', $settings->emails ?? []) as $email)
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-white"><i class="fas fa-envelope fa-xs text-muted"></i></span>
                                            <input type="email" class="form-control bg-white" name="emails[]" value="{{ $email }}" placeholder="email@contoh.com">
                                            <button class="btn btn-white text-danger border" type="button" onclick="this.closest('.input-group').remove()"><i class="fas fa-times"></i></button>
                                        </div>
                                        @empty
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-white"><i class="fas fa-envelope fa-xs text-muted"></i></span>
                                            <input type="email" class="form-control bg-white" name="emails[]" placeholder="email@contoh.com">
                                            <button class="btn btn-white text-danger border" type="button" onclick="this.closest('.input-group').remove()"><i class="fas fa-times"></i></button>
                                        </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>

                            <!-- Working Hours -->
                            <div class="col-md-4">
                                <div class="p-3 bg-light rounded-3 h-100 border border-light">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <label class="form-label fw-bold text-dark mb-0"><i class="fas fa-clock me-2 text-info"></i> Jam Layanan</label>
                                        <button type="button" class="btn btn-sm btn-light shadow-sm text-primary rounded-circle" onclick="addItem('working-hours-container', 'working_hours[]', 'fa-clock')" title="Tambah"><i class="fas fa-plus"></i></button>
                                    </div>
                                    <div id="working-hours-container" class="vstack gap-2">
                                        @forelse(old('working_hours', $settings->working_hours ?? []) as $hours)
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-white"><i class="fas fa-clock fa-xs text-muted"></i></span>
                                            <input type="text" class="form-control bg-white" name="working_hours[]" value="{{ $hours }}" placeholder="Senin - Jumat: 08:00 - 16:00">
                                            <button class="btn btn-white text-danger border" type="button" onclick="this.closest('.input-group').remove()"><i class="fas fa-times"></i></button>
                                        </div>
                                        @empty
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-white"><i class="fas fa-clock fa-xs text-muted"></i></span>
                                            <input type="text" class="form-control bg-white" name="working_hours[]" placeholder="Senin - Jumat: 08:00 - 16:00">
                                            <button class="btn btn-white text-danger border" type="button" onclick="this.closest('.input-group').remove()"><i class="fas fa-times"></i></button>
                                        </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 3: Social Media -->
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold text-danger"><i class="fas fa-share-alt me-2"></i> Jejaring Sosial</h6>
                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm" onclick="addSocialItem()">
                            <i class="fas fa-plus me-1"></i> Tambah Akun
                        </button>
                    </div>
                    <div class="card-body p-4 bg-light">
                        <div id="social-container" class="row g-3">
                            @php
                                $socials = old('social_media', $settings->social_media ?? []);
                                if (!is_array($socials)) $socials = [];
                            @endphp
                            
                            @foreach($socials as $index => $social)
                            <div class="col-md-6 col-xl-4 social-item">
                                <div class="card h-100 border-0 shadow-sm hover-shadow transition-all">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div class="badge bg-light text-dark border p-2 rounded-circle">
                                                <i class="fas fa-hashtag fa-lg"></i>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="this.closest('.social-item').remove()" title="Hapus">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                        
                                        <div class="mb-2">
                                            <select class="form-select form-select-sm fw-bold border-0 bg-light mb-2" name="social_media[{{ $index }}][platform]" onchange="updateSocialIcon(this)">
                                                <option value="instagram" {{ ($social['platform'] ?? '') == 'instagram' ? 'selected' : '' }}>Instagram</option>
                                                <option value="facebook" {{ ($social['platform'] ?? '') == 'facebook' ? 'selected' : '' }}>Facebook</option>
                                                <option value="youtube" {{ ($social['platform'] ?? '') == 'youtube' ? 'selected' : '' }}>Youtube</option>
                                                <option value="twitter" {{ ($social['platform'] ?? '') == 'twitter' ? 'selected' : '' }}>Twitter / X</option>
                                                <option value="tiktok" {{ ($social['platform'] ?? '') == 'tiktok' ? 'selected' : '' }}>TikTok</option>
                                                <option value="website" {{ ($social['platform'] ?? '') == 'website' ? 'selected' : '' }}>Website</option>
                                            </select>
                                        </div>
                                        
                                        <div class="vstack gap-2">
                                            <input type="text" class="form-control form-control-sm border-light bg-light" name="social_media[{{ $index }}][name]" value="{{ $social['name'] ?? '' }}" placeholder="Nama Akun (e.g. Pemkab Empat Lawang)">
                                            <input type="text" class="form-control form-control-sm border-light bg-light" name="social_media[{{ $index }}][username]" value="{{ $social['username'] ?? '' }}" placeholder="Username (e.g. @pemkab4l)">
                                            <input type="url" class="form-control form-control-sm border-light bg-light" name="social_media[{{ $index }}][url]" value="{{ $social['url'] ?? '' }}" placeholder="https://...">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        
                        @if(count($socials) == 0)
                        <div id="empty-social-state" class="text-center py-5 text-muted">
                            <i class="fas fa-users-slash fa-3x mb-3 opacity-25"></i>
                            <p class="small mb-0">Belum ada akun sosial media yang ditambahkan.</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="card border-0 shadow-sm rounded-4 mt-4 mb-5">
            <div class="card-body p-4 d-flex justify-content-end align-items-center">
                <button type="reset" class="btn btn-light rounded-pill px-4 fw-bold me-2 text-black">
                    <i class="fas fa-undo me-2"></i> Reset
                </button>
                <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm hover-scale py-2">
                    <i class="fas fa-save me-2"></i> Simpan Perubahan
                </button>
            </div>
        </div>
    </form>
</div>

@push('styles')
<style>
    .ls-1 { letter-spacing: 1px; }
    .shadow-inner { box-shadow: inset 0 2px 4px 0 rgba(0,0,0,0.06); }
    .hover-shadow:hover { box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; transform: translateY(-2px); }
    .transition-all { transition: all 0.2s ease-in-out; }
    .btn-white { background: #fff; }
    .btn-white:hover { background: #f8f9fa; }
    
    /* Responsive sticky footer adjustment */
    @media (max-width: 991.98px) {
        .position-fixed[style*="margin-left"] {
            margin-left: 0 !important;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    function addItem(containerId, inputName, iconClass = 'fa-circle') {
        const container = document.getElementById(containerId);
        const div = document.createElement('div');
        div.className = 'input-group input-group-sm';
        div.innerHTML = `
            <span class="input-group-text bg-white"><i class="fas ${iconClass} fa-xs text-muted"></i></span>
            <input type="text" class="form-control bg-white" name="${inputName}" placeholder="Isi data...">
            <button class="btn btn-white text-danger border" type="button" onclick="this.closest('.input-group').remove()"><i class="fas fa-times"></i></button>
        `;
        container.appendChild(div);
    }

    function addSocialItem() {
        const container = document.getElementById('social-container');
        const emptyState = document.getElementById('empty-social-state');
        if(emptyState) emptyState.style.display = 'none';

        const uniqueIndex = Date.now(); 
        
        const col = document.createElement('div');
        col.className = 'col-md-6 col-xl-4 social-item';
        col.innerHTML = `
            <div class="card h-100 border-0 shadow-sm hover-shadow transition-all">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="badge bg-light text-dark border p-2 rounded-circle">
                            <i class="fas fa-hashtag fa-lg"></i>
                        </div>
                        <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="removeSocialItem(this)" title="Hapus">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <div class="mb-2">
                        <label class="form-label x-small text-muted text-uppercase fw-bold mb-1">Platform</label>
                        <select class="form-select form-select-sm fw-bold border-0 bg-light mb-2" name="social_media[${uniqueIndex}][platform]" onchange="updateSocialIcon(this)">
                            <option value="instagram">Instagram</option>
                            <option value="facebook">Facebook</option>
                            <option value="youtube">Youtube</option>
                            <option value="twitter">Twitter / X</option>
                            <option value="tiktok">TikTok</option>
                            <option value="website">Website</option>
                        </select>
                    </div>
                    
                    <div class="vstack gap-2">
                        <div>
                            <label class="form-label x-small text-muted text-uppercase fw-bold mb-1">Nama Akun</label>
                            <input type="text" class="form-control form-control-sm border-light bg-light" name="social_media[${uniqueIndex}][name]" placeholder="Nama Akun">
                        </div>
                        <div>
                            <label class="form-label x-small text-muted text-uppercase fw-bold mb-1">Username</label>
                            <input type="text" class="form-control form-control-sm border-light bg-light" name="social_media[${uniqueIndex}][username]" placeholder="Username">
                        </div>
                        <div>
                            <label class="form-label x-small text-muted text-uppercase fw-bold mb-1">Link / URL Profil</label>
                            <input type="url" class="form-control form-control-sm border-light bg-light" name="social_media[${uniqueIndex}][url]" placeholder="https://...">
                        </div>
                    </div>
                </div>
            </div>
        `;
        container.appendChild(col);
        
        // Trigger icon update for default
        updateSocialIcon(col.querySelector('select'));
    }

    function removeSocialItem(btn) {
        btn.closest('.social-item').remove();
        const container = document.getElementById('social-container');
        const emptyState = document.getElementById('empty-social-state');
        if(container.children.length === 0 && emptyState) {
            emptyState.style.display = 'block';
        }
    }

    function updateSocialIcon(select) {
        const card = select.closest('.card');
        const iconBadge = card.querySelector('.badge');
        const icon = iconBadge.querySelector('i');
        
        // Reset classes
        icon.className = '';
        iconBadge.className = 'badge p-2 rounded-circle border ';
        
        switch(select.value) {
            case 'instagram':
                icon.className = 'fab fa-instagram fa-lg';
                iconBadge.classList.add('bg-danger', 'bg-opacity-10', 'text-danger', 'border-danger');
                break;
            case 'facebook':
                icon.className = 'fab fa-facebook fa-lg';
                iconBadge.classList.add('bg-primary', 'bg-opacity-10', 'text-primary', 'border-primary');
                break;
            case 'youtube':
                icon.className = 'fab fa-youtube fa-lg';
                iconBadge.classList.add('bg-danger', 'bg-opacity-10', 'text-danger', 'border-danger');
                break;
            case 'twitter':
                icon.className = 'fab fa-twitter fa-lg';
                iconBadge.classList.add('bg-dark', 'bg-opacity-10', 'text-dark', 'border-dark');
                break;
            case 'tiktok':
                icon.className = 'fab fa-tiktok fa-lg';
                iconBadge.classList.add('bg-dark', 'bg-opacity-10', 'text-dark', 'border-dark');
                break;
            default:
                icon.className = 'fas fa-globe fa-lg';
                iconBadge.classList.add('bg-secondary', 'bg-opacity-10', 'text-secondary', 'border-secondary');
        }
    }

    // Initialize icons on load
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('select[name*="[platform]"]').forEach(select => {
            updateSocialIcon(select);
        });
    });
</script>
@endpush
@endsection