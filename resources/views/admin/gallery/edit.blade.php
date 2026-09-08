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

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
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
                            <input type="hidden" name="is_published" value="0">
                            <input type="checkbox" class="form-check-input" id="is_published" name="is_published" value="1" {{ old('is_published', $gallery->is_published) ? 'checked' : '' }}>
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
            <form id="uploadForm" data-url="{{ route('admin.galleries.upload-chunk', $gallery->id) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="photos" class="form-label">Pilih Foto (Bisa banyak sekaligus)</label>
                        <input type="file" class="form-control" id="photos" name="photos[]" multiple accept="image/*" required>
                        <small class="text-muted d-block mt-1">
                            Format: jpg, png, gif, webp. Maks 20MB per foto.
                            File dipotong otomatis jadi bagian kecil, jadi batas server
                            ({{ ini_get('upload_max_filesize') }}/{{ ini_get('post_max_size') }}) tidak jadi masalah.
                        </small>
                    </div>
                    <div id="uploadProgress" class="d-none">
                        <div class="d-flex justify-content-between small mb-1">
                            <span id="uploadLabel"></span>
                            <span id="uploadPercent"></span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div id="uploadBar" class="progress-bar" role="progressbar" style="width: 0%"></div>
                        </div>
                    </div>
                    <div id="uploadErrors" class="alert alert-danger py-2 px-3 small mt-3 d-none"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="uploadSubmit">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Photos are sliced client-side and sent one chunk per request, so a single
// request never approaches upload_max_filesize / post_max_size.
(function () {
    var CHUNK = 1024 * 1024; // 1MB — safely under the smallest common PHP limit
    var form = document.getElementById('uploadForm');
    if (!form) return;

    var input = document.getElementById('photos');
    var submit = document.getElementById('uploadSubmit');
    var box = document.getElementById('uploadProgress');
    var bar = document.getElementById('uploadBar');
    var label = document.getElementById('uploadLabel');
    var percent = document.getElementById('uploadPercent');
    var errors = document.getElementById('uploadErrors');
    var token = form.querySelector('input[name="_token"]').value;

    function randomId() {
        return Date.now().toString(36) + Math.random().toString(36).slice(2, 12);
    }

    function sendChunk(file, uploadId, index, total) {
        var body = new FormData();
        body.append('_token', token);
        body.append('upload_id', uploadId);
        body.append('index', index);
        body.append('total', total);
        body.append('chunk', file.slice(index * CHUNK, (index + 1) * CHUNK));

        return fetch(form.dataset.url, { method: 'POST', body: body, credentials: 'same-origin' })
            .then(function (res) {
                return res.json().catch(function () { return {}; }).then(function (data) {
                    if (!res.ok) throw new Error(data.message || 'Upload gagal (HTTP ' + res.status + ').');
                    return data;
                });
            });
    }

    function uploadFile(file, done, totalFiles) {
        var uploadId = randomId();
        var chunks = Math.max(1, Math.ceil(file.size / CHUNK));
        var chain = Promise.resolve();

        for (var i = 0; i < chunks; i++) {
            (function (index) {
                chain = chain.then(function () {
                    return sendChunk(file, uploadId, index, chunks);
                }).then(function () {
                    var overall = ((done + (index + 1) / chunks) / totalFiles) * 100;
                    bar.style.width = overall.toFixed(0) + '%';
                    percent.textContent = overall.toFixed(0) + '%';
                    label.textContent = 'Mengunggah ' + file.name + ' (' + (done + 1) + '/' + totalFiles + ')';
                });
            })(i);
        }

        return chain;
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var files = Array.from(input.files);
        if (!files.length) return;

        submit.disabled = true;
        input.disabled = true;
        errors.classList.add('d-none');
        errors.textContent = '';
        box.classList.remove('d-none');
        bar.style.width = '0%';

        var failed = [];
        var chain = Promise.resolve();

        files.forEach(function (file, i) {
            chain = chain
                .then(function () { return uploadFile(file, i, files.length); })
                .catch(function (err) { failed.push(file.name + ': ' + err.message); });
        });

        chain.then(function () {
            if (failed.length === files.length) {
                submit.disabled = false;
                input.disabled = false;
                box.classList.add('d-none');
                errors.classList.remove('d-none');
                errors.innerHTML = failed.join('<br>');
                return;
            }
            // reload so the photo grid and ordering reflect what actually landed
            if (failed.length) sessionStorage.setItem('galleryUploadFailed', failed.join('
'));
            window.location.reload();
        });
    });

    // the modal is closed after the reload, so surface leftovers on the page itself
    var leftover = sessionStorage.getItem('galleryUploadFailed');
    if (leftover) {
        sessionStorage.removeItem('galleryUploadFailed');
        var alertBox = document.createElement('div');
        alertBox.className = 'alert alert-warning alert-dismissible fade show';
        alertBox.innerHTML = 'Sebagian foto gagal diunggah:<br>' + leftover.split('
').join('<br>') +
            '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        var container = document.querySelector('.container-fluid');
        if (container) container.insertBefore(alertBox, container.children[1]);
    }
})();

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
