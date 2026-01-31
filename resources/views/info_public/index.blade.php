@extends('layouts.app')

@section('title', 'Informasi Publik')

@section('content')
<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1>Informasi Publik</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Informasi Publik</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="container pb-5">
    <div class="row mb-5">
        <div class="col-md-12">
            <div class="card card-hover shadow-sm p-3">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <ul class="nav nav-pills" id="category-tabs">
                            <li class="nav-item">
                                <a class="nav-link {{ !request('category') ? 'active' : '' }}" href="#" data-category="">Semua</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request('category') == 'berkala' ? 'active' : '' }}" href="#" data-category="berkala">Berkala</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request('category') == 'serta-merta' ? 'active' : '' }}" href="#" data-category="serta-merta">Serta Merta</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request('category') == 'setiap-saat' ? 'active' : '' }}" href="#" data-category="setiap-saat">Setiap Saat</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request('category') == 'dikecualikan' ? 'active' : '' }}" href="#" data-category="dikecualikan">Dikecualikan</a>
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-4 mt-3 mt-md-0">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" class="form-control border-start-0 ps-0" id="search-input" placeholder="Cari informasi...">
                            <button class="btn btn-primary" type="button" id="search-btn">Cari</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-hover border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="d-flex justify-content-center p-5 d-none" id="loading-spinner">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-custom mb-0" id="info-table">
                    <thead>
                        <tr>
                            <th class="ps-4">Judul Informasi</th>
                            <th>Deskripsi</th>
                            <th>Tanggal</th>
                            <th class="pe-4 text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="info-list">
                        @include('info_public._list')
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .pagination { justify-content: center; margin-top: 30px; margin-bottom: 30px; }
    .nav-pills .nav-link.active { color: #fff !important; }
</style>
@endpush

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        let currentCategory = '{{ request("category") }}';
        let currentSearch = '';

        function fetchInfo(page = 1) {
            $('#info-list').css('opacity', '0.5');
            $('#loading-spinner').removeClass('d-none');
            
            $.ajax({
                url: "{{ route('informasi-publik.index') }}",
                data: {
                    category: currentCategory,
                    search: currentSearch,
                    page: page
                },
                success: function(data) {
                    $('#info-list').html(data);
                    $('#info-list').css('opacity', '1');
                    $('#loading-spinner').addClass('d-none');
                },
                error: function() {
                    alert('Gagal memuat data. Silakan coba lagi.');
                    $('#info-list').css('opacity', '1');
                    $('#loading-spinner').addClass('d-none');
                }
            });
        }

        // Handle Category Click
        $('#category-tabs .nav-link').click(function(e) {
            e.preventDefault();
            $('#category-tabs .nav-link').removeClass('active');
            $(this).addClass('active');
            currentCategory = $(this).data('category');
            fetchInfo();
        });

        // Handle Search
        $('#search-btn').click(function() {
            currentSearch = $('#search-input').val();
            fetchInfo();
        });

        $('#search-input').keypress(function(e) {
            if(e.which == 13) {
                currentSearch = $(this).val();
                fetchInfo();
            }
        });

        // Handle Pagination
        $(document).on('click', '.pagination a', function(e) {
            e.preventDefault();
            let page = $(this).attr('href').split('page=')[1];
            fetchInfo(page);
        });
    });
</script>
@endsection
