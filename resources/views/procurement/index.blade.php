@extends('layouts.app')

@section('content')
<h2 class="mb-4">Informasi Pengadaan Barang & Jasa</h2>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Judul</th>
                        <th>Kategori</th>
                        <th>Keterangan</th>
                        <th>File</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($procurements as $item)
                    <tr>
                        <td>{{ $item->title }}</td>
                        <td>
                            <span class="badge bg-secondary">{{ ucwords(str_replace('_', ' ', $item->category)) }}</span>
                        </td>
                        <td>{{ $item->content }}</td>
                        <td>
                            @if($item->file_path)
                            <a href="{{ asset('storage/' . $item->file_path) }}" class="btn btn-sm btn-success" target="_blank">Download</a>
                            @else
                            -
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center">Tidak ada data pengadaan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    {{ $procurements->links() }}
</div>
@endsection
