@extends('layouts.admin')

@section('title', 'Manajemen Pejabat & Pejabat Struktural')

@section('content')
<div class="card border-0 shadow-lg rounded-4 overflow-hidden">
    <div class="card-header bg-gradient bg-primary text-white py-4 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold"><i class="fas fa-user-tie me-2"></i>Pejabat & Pejabat Struktural</h5>
        <button type="button" class="btn btn-light shadow-sm fw-bold text-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#createModal">
            <i class="fas fa-plus-circle me-2"></i> Tambah Pejabat
        </button>
    </div>

    <div class="card-body p-0">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show m-4 shadow-sm border-0 border-start border-5 border-success" role="alert">
                <strong>Berhasil!</strong> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-uppercase text-secondary small">
                    <tr>
                        <th class="px-4 py-3 border-0" style="width: 70px;">Foto</th>
                        <th class="px-4 py-3 border-0">Nama</th>
                        <th class="px-4 py-3 border-0">Jabatan</th>
                        <th class="px-4 py-3 border-0" style="width: 90px;">Urutan</th>
                        <th class="px-4 py-3 border-0" style="width: 120px;">Status</th>
                        <th class="px-4 py-3 border-0 text-end" style="width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($officials as $official)
                        <tr>
                            <td class="px-4">
                                @if ($official->photo)
                                    <img src="{{ storage_url($official->photo) }}" class="rounded-circle" width="48" height="48" style="object-fit: cover;" alt="{{ $official->name }}">
                                @else
                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                                        <i class="fas fa-user text-secondary"></i>
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 fw-bold">{{ $official->name }}</td>
                            <td class="px-4">{{ $official->position }}</td>
                            <td class="px-4">{{ $official->order }}</td>
                            <td class="px-4">
                                <form action="{{ route('admin.officials.toggle-status', $official->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-sm p-0 border-0 bg-transparent">
                                        @if ($official->is_published)
                                            <span class="badge bg-success">Published</span>
                                        @else
                                            <span class="badge bg-secondary">Draft</span>
                                        @endif
                                    </button>
                                </form>
                            </td>
                            <td class="px-4 text-end">
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-circle" data-bs-toggle="modal" data-bs-target="#editModal{{ $official->id }}" title="Edit">
                                    <i class="fas fa-pen"></i>
                                </button>
                                <form action="{{ route('admin.officials.destroy', $official->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus data pejabat ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editModal{{ $official->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('admin.officials.update', $official->id) }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header">
                                            <h5 class="modal-title"><i class="fas fa-pen me-2 text-primary"></i>Edit Pejabat</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Nama</label>
                                                <input type="text" name="name" class="form-control" value="{{ $official->name }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Jabatan</label>
                                                <input type="text" name="position" class="form-control" value="{{ $official->position }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Bio Singkat</label>
                                                <textarea name="bio" class="form-control" rows="3">{{ $official->bio }}</textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Urutan Tampil</label>
                                                <input type="number" name="order" class="form-control" value="{{ $official->order }}" min="0">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Foto</label>
                                                @if ($official->photo)
                                                    <div class="mb-2">
                                                        <img src="{{ storage_url($official->photo) }}" class="img-thumbnail" style="max-height: 120px">
                                                    </div>
                                                @endif
                                                <input type="file" name="photo" class="form-control" accept="image/*">
                                                <div class="form-text">Upload foto baru untuk mengganti yang lama.</div>
                                            </div>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="is_published" value="1" {{ $official->is_published ? 'checked' : '' }}>
                                                <label class="form-check-label">Tampilkan di halaman publik</label>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">Belum ada data pejabat. Klik "Tambah Pejabat" untuk menambahkan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.officials.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus-circle me-2 text-primary"></i>Tambah Pejabat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Jabatan</label>
                        <input type="text" name="position" class="form-control" placeholder="mis. Bupati, Wakil Bupati, Sekretaris Daerah" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Bio Singkat</label>
                        <textarea name="bio" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Urutan Tampil</label>
                        <input type="number" name="order" class="form-control" value="0" min="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Foto</label>
                        <input type="file" name="photo" class="form-control" accept="image/*">
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_published" value="1" checked>
                        <label class="form-check-label">Tampilkan di halaman publik</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
