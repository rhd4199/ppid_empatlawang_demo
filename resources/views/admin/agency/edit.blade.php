@extends('layouts.admin')

@section('title', 'Ubah Instansi')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Ubah Instansi</h1>
        <a href="{{ route('admin.agencies.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('admin.agencies.update', $item->id) }}" method="POST">
                @csrf
                @method('PUT')
                @include('admin.agency._form')
            </form>
        </div>
    </div>
</div>
@endsection
