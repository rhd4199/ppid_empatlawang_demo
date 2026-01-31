@extends('layouts.admin')

@section('title', 'Edit Album Galeri')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Album: {{ $gallery->title }}</h1>
        <a href="{{ route('admin.galleries.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left fa-sm"></i> Kembali
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <!-- Edit Metadata -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Detail Album</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.galleries.update', $gallery->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="title" class="form-label">Judul Album</label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $gallery->title) }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Deskripsi</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $gallery->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="cover_image" class="form-label">Ganti Sampul (Opsional)</label>
                            <input type="file" class="form-control @error('cover_image') is-invalid @enderror" id="cover_image" name="cover_image" accept="image/*" onchange="previewCover(this)">
                            @error('cover_image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="mt-2">
                                <label class="small text-muted">Sampul Saat Ini:</label><br>
                                <img src="{{ Storage::url($gallery->cover_image) }}" alt="Current Cover" class="img-fluid rounded mb-2" style="max-height: 150px;">
                                <img id="cover_preview" src="#" alt="Cover Preview" style="display: none; max-height: 150px; max-width: 100%;" class="mt-2 rounded">
                            </div>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="is_published" name="is_published" {{ old('is_published', $gallery->is_published) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_published">Publikasikan Album</label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Simpan Perubahan Album</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Manage Photos -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Kelola Foto ({{ $gallery->items->count() }})</h6>
                    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#uploadModal">
                        <i class="fas fa-upload fa-sm"></i> Tambah Foto
                    </button>
                </div>
                <div class="card-body">
                    @if($gallery->items->count() > 0)
                        <form action="{{ route('admin.galleries.update-order', $gallery->id) }}" method="POST">
                            @csrf
                            <div class="row" id="photo-grid">
                                @foreach($gallery->items as $item)
                                    <div class="col-md-4 col-sm-6 mb-4">
                                        <div class="card h-100 border shadow-sm">
                                            <div class="ratio ratio-4x3">
                                                <img src="{{ Storage::url($item->image_path) }}" class="card-img-top object-fit-cover" alt="Foto">
                                            </div>
                                            <div class="card-body p-2">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div class="input-group input-group-sm me-2">
                                                        <span class="input-group-text">Urutan</span>
                                                        <input type="number" name="orders[{{ $item->id }}]" class="form-control" value="{{ $item->order }}" min="0">
                                                    </div>
                                                    <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deletePhotoModal{{ $item->id }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Simpan Urutan
                                </button>
                            </div>
                        </form>

                        <!-- Correctly placed Delete Forms (outside the main form) -->
                        @foreach($gallery->items as $item)
                             <div class="modal fade" id="deletePhotoModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-sm">
                                    <div class="modal-content">
                                        <div class="modal-body text-center p-4">
                                            <i class="fas fa-exclamation-circle text-warning fa-3x mb-3"></i>
                                            <p>Hapus foto ini?</p>
                                            <div class="d-flex justify-content-center gap-2">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <form action="{{ route('admin.galleries.delete-photo', $item->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger">Hapus</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-images fa-3x mb-3"></i>
                            <p>Belum ada foto dalam album ini.</p>
                            <button class="btn btn-primary btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#uploadModal">
                                Upload Foto Sekarang
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload Foto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.galleries.upload-photos', $gallery->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="photos" class="form-label">Pilih Foto (Bisa banyak sekaligus)</label>
                        <input type="file" class="form-control" id="photos" name="photos[]" multiple accept="image/*" required>
                        <small class="text-muted">Format: jpg, jpeg, png, gif. Max: 10MB per file.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function previewCover(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('cover_preview').src = e.target.result;
            document.getElementById('cover_preview').style.display = 'block';
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection
