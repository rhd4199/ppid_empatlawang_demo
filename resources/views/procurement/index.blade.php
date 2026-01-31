@extends('layouts.app')

@section('title', 'Pengadaan Barang & Jasa')

@section('content')
<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1>Pengadaan Barang & Jasa</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Pengadaan Barang & Jasa</li>
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
                    <a href="#info" class="list-group-item list-group-item-action active py-3" data-bs-toggle="list">
                        <i class="fas fa-info-circle me-2 w-20"></i> Informasi Pengadaan
                    </a>
                    <a href="#regulasi" class="list-group-item list-group-item-action py-3" data-bs-toggle="list">
                        <i class="fas fa-gavel me-2 w-20"></i> Regulasi/Aturan
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <div class="tab-content" style="min-height: 60vh;">
                <!-- Informasi Pengadaan -->
                <div class="tab-pane fade show active" id="info">
                    <div class="card card-hover border-0 shadow-sm p-4 mb-4">
                        <h3 class="mb-4 border-bottom pb-2">Informasi Pengadaan</h3>
                        <div class="list-group list-group-flush">
                            @forelse($documents->where('category', 'pengadaan_info') as $doc)
                            <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                                <div>
                                    <h5 class="mb-1">{{ $doc->title }}</h5>
                                    <p class="mb-0 text-muted small">{{ $doc->description }}</p>
                                </div>
                                @if($doc->file_path)
                                <a href="{{ asset('storage/' . $doc->file_path) }}" class="btn btn-sm btn-primary rounded-pill px-3" target="_blank">
                                    <i class="fas fa-eye me-1"></i> Lihat
                                </a>
                                @endif
                            </div>
                            @empty
                            <div class="alert alert-info">Belum ada informasi pengadaan.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Regulasi -->
                <div class="tab-pane fade" id="regulasi">
                    <div class="card card-hover border-0 shadow-sm p-4 mb-4">
                        <h3 class="mb-4 border-bottom pb-2">Regulasi & Aturan</h3>
                        <div class="list-group list-group-flush">
                            @forelse($documents->where('category', 'pengadaan_regulasi') as $doc)
                            <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                                <div>
                                    <h5 class="mb-1">{{ $doc->title }}</h5>
                                    <p class="mb-0 text-muted small">{{ $doc->description }}</p>
                                </div>
                                @if($doc->file_path)
                                <a href="{{ asset('storage/' . $doc->file_path) }}" class="btn btn-sm btn-primary rounded-pill px-3" target="_blank">
                                    <i class="fas fa-gavel me-1"></i> Lihat
                                </a>
                                @endif
                            </div>
                            @empty
                            <div class="alert alert-info">Belum ada dokumen regulasi.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle hash in URL for tabs
        var hash = window.location.hash;
        var tabTrigger = document.querySelector('.list-group-item[href="' + hash + '"]');
        if (tabTrigger) {
            var tab = new bootstrap.Tab(tabTrigger);
            tab.show();
        }

        // Update hash when tab changes
        var tabElements = document.querySelectorAll('[data-bs-toggle="list"]');
        tabElements.forEach(function(tabEl) {
            tabEl.addEventListener('shown.bs.tab', function(event) {
                var hash = event.target.getAttribute('href');
                history.pushState(null, null, hash);
            });
        });
        
        // Handle query param 'tab' for initial load if no hash
        if (!hash) {
            const urlParams = new URLSearchParams(window.location.search);
            const tabParam = urlParams.get('tab');
            if (tabParam) {
                const targetHash = '#' + tabParam;
                var tabTrigger = document.querySelector('.list-group-item[href="' + targetHash + '"]');
                if (tabTrigger) {
                    var tab = new bootstrap.Tab(tabTrigger);
                    tab.show();
                    history.replaceState(null, null, targetHash);
                }
            }
        }
    });
</script>
@endpush
@endsection
