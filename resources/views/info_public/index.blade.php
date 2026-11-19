@extends('layouts.app')

@section('title', 'Informasi Publik')
@section('description', 'Daftar informasi publik Kabupaten Empat Lawang: informasi berkala, serta merta, setiap saat, dan informasi yang dikecualikan.')

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
                                <a class="nav-link {{ !request('category') ? 'active' : '' }}" href="{{ route('informasi-publik.index', array_filter(['category' => ''])) }}" data-category="">Semua</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request('category') == 'berkala' ? 'active' : '' }}" href="{{ route('informasi-publik.index', array_filter(['category' => 'berkala'])) }}" data-category="berkala">Berkala</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request('category') == 'serta-merta' ? 'active' : '' }}" href="{{ route('informasi-publik.index', array_filter(['category' => 'serta-merta'])) }}" data-category="serta-merta">Serta Merta</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request('category') == 'setiap-saat' ? 'active' : '' }}" href="{{ route('informasi-publik.index', array_filter(['category' => 'setiap-saat'])) }}" data-category="setiap-saat">Setiap Saat</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request('category') == 'dikecualikan' ? 'active' : '' }}" href="{{ route('informasi-publik.index', array_filter(['category' => 'dikecualikan'])) }}" data-category="dikecualikan">Dikecualikan</a>
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-4 mt-3 mt-md-0">
                        {{-- Plain GET form: works without JS; the script below upgrades it to live search. --}}
                        <form action="{{ route('informasi-publik.index') }}" method="GET" role="search" id="search-form">
                            <input type="hidden" name="category" value="{{ request('category') }}" id="search-category">
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                                <input type="search" name="search" class="form-control" id="search-input" value="{{ request('search') }}"
                                       placeholder="Cari judul atau deskripsi..." aria-label="Cari informasi publik" maxlength="100" autocomplete="off">
                                <button class="btn btn-primary" type="submit">Cari</button>
                            </div>
                        </form>
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

@push('scripts')
<script>
    (function () {
        const list = document.getElementById('info-list');
        const form = document.getElementById('search-form');
        const input = document.getElementById('search-input');
        const categoryField = document.getElementById('search-category');
        const spinner = document.getElementById('loading-spinner');
        const baseUrl = form.action;
        let timer, controller;

        function load(params) {
            const query = new URLSearchParams(Object.entries(params).filter(([, v]) => v));
            controller?.abort();
            controller = new AbortController();
            list.style.opacity = '.5';
            spinner.classList.remove('d-none');
            fetch(baseUrl + (query.size ? '?' + query : ''), { headers: { 'X-Requested-With': 'XMLHttpRequest' }, signal: controller.signal })
                .then(r => { if (!r.ok) throw new Error(r.status); return r.text(); })
                .then(html => {
                    list.innerHTML = html;
                    history.replaceState(null, '', baseUrl + (query.size ? '?' + query : ''));
                })
                .catch(e => { if (e.name !== 'AbortError') list.innerHTML = '<tr><td colspan="4" class="text-center py-5 text-danger">Gagal memuat data. Silakan coba lagi.</td></tr>'; })
                .finally(() => { list.style.opacity = '1'; spinner.classList.add('d-none'); });
        }

        const current = (page) => ({ category: categoryField.value, search: input.value.trim(), page });

        document.querySelectorAll('#category-tabs .nav-link').forEach(link => link.addEventListener('click', e => {
            e.preventDefault();
            document.querySelectorAll('#category-tabs .nav-link').forEach(l => l.classList.remove('active'));
            link.classList.add('active');
            categoryField.value = link.dataset.category;
            load(current());
        }));

        form.addEventListener('submit', e => { e.preventDefault(); clearTimeout(timer); load(current()); });
        // Live search while typing (also fires when the clear button empties the field).
        input.addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(() => load(current()), 350); });

        list.addEventListener('click', e => {
            const link = e.target.closest('.pagination a');
            if (!link) return;
            e.preventDefault();
            load(current(new URL(link.href).searchParams.get('page')));
        });
    })();
</script>
@endpush
@endsection
