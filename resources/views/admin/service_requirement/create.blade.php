@extends('layouts.admin')

@section('title', 'Tambah Persyaratan Layanan')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Tambah Persyaratan Layanan</h1>
        <a href="{{ route('admin.service-requirements.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('admin.service-requirements.store') }}" method="POST">
                @csrf
                @include('admin.service_requirement._form')
            </form>
        </div>
    </div>
</div>
@endsection
