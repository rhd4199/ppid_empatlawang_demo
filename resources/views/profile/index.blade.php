@extends('layouts.app')

@section('content')
<h2 class="mb-4">Profil Pemerintah</h2>
<div class="row">
    @foreach($profiles as $profile)
    <div class="col-md-4 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">{{ $profile->title }}</h5>
                <p class="card-text">{{ Str::limit(strip_tags($profile->content), 100) }}</p>
                <a href="{{ route('profile.show', $profile->slug) }}" class="btn btn-primary">Lihat Detail</a>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection
