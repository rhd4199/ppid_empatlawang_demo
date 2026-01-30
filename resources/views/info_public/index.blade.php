@extends('layouts.app')

@section('content')
<h2 class="mb-4">Informasi Publik</h2>

<ul class="nav nav-tabs mb-4">
  <li class="nav-item">
    <a class="nav-link {{ request('category') == 'berkala' ? 'active' : '' }}" href="{{ route('informasi-publik.index', ['category' => 'berkala']) }}">Berkala</a>
  </li>
  <li class="nav-item">
    <a class="nav-link {{ request('category') == 'serta_merta' ? 'active' : '' }}" href="{{ route('informasi-publik.index', ['category' => 'serta_merta']) }}">Serta Merta</a>
  </li>
  <li class="nav-item">
    <a class="nav-link {{ request('category') == 'setiap_saat' ? 'active' : '' }}" href="{{ route('informasi-publik.index', ['category' => 'setiap_saat']) }}">Setiap Saat</a>
  </li>
  <li class="nav-item">
    <a class="nav-link {{ request('category') == 'dikecualikan' ? 'active' : '' }}" href="{{ route('informasi-publik.index', ['category' => 'dikecualikan']) }}">Dikecualikan</a>
  </li>
</ul>

<div class="card">
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Judul Informasi</th>
                    <th>Deskripsi</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($infos ?? [] as $info)
                <tr>
                    <td>{{ $info->title }}</td>
                    <td>{{ Str::limit($info->description, 50) }}</td>
                    <td>{{ $info->published_date }}</td>
                    <td>
                        <a href="#" class="btn btn-sm btn-info text-white">Detail</a>
                        @if($info->file_path)
                        <a href="{{ asset('storage/' . $info->file_path) }}" class="btn btn-sm btn-success" target="_blank">Download</a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center">Tidak ada informasi untuk kategori ini.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if(isset($infos))
        {{ $infos->links() }}
        @endif
    </div>
</div>
@endsection
