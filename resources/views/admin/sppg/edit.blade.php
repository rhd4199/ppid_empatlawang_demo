@extends('layouts.admin')

@section('title', 'Ubah SPPG')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Ubah SPPG</h1>
        <a href="{{ route('admin.sppg.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('admin.sppg.update', $item->id) }}" method="POST">
                @csrf
                @method('PUT')
                @include('admin.sppg._form')
            </form>
        </div>
    </div>
</div>
@endsection
