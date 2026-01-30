@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $profile->title }}</li>
            </ol>
        </nav>
        
        <div class="card shadow-sm">
            <div class="card-body">
                <h1 class="card-title border-bottom pb-2">{{ $profile->title }}</h1>
                <div class="mt-4">
                    {!! $profile->content !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
