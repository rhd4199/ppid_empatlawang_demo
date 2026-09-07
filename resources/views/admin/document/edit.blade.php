@extends('layouts.admin')

@section('title', 'Edit Dokumen: ' . $document->title)

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<style>
    .note-editor .note-toolbar { background: #f8f9fa; border-bottom: 1px solid #dee2e6; }
    .note-editor.note-frame { border: 1px solid #dee2e6; border-radius: 0.375rem; }
</style>
@endpush

@section('content')
<div class="card border-0 shadow-lg rounded-4 overflow-hidden">
    <div class="card-header bg-gradient bg-primary text-white py-4">
        <h5 class="mb-0 fw-bold"><i class="fas fa-edit me-2"></i>Edit Dokumen</h5>
    </div>
    <div class="card-body p-4">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route($updateRoute, $document->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="form-label fw-bold">Judul Dokumen</label>
                <input type="text" name="title" class="form-control form-control-lg" value="{{ old('title', $document->title) }}" required>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">Kategori</label>
                <select name="category" class="form-select" required>
                    @foreach ($categories as $value => $label)
                        <option value="{{ $value }}" {{ old('category', $document->category) == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">Deskripsi</label>
                <textarea id="description" name="description" class="form-control" rows="10">{{ old('description', $document->description) }}</textarea>
                <div class="form-text text-muted">Gunakan editor di atas untuk memformat teks, tabel, atau menyisipkan gambar (bisa paste langsung).</div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">File Dokumen</label>
                @if ($document->file_path)
                    <div class="alert alert-light border d-flex justify-content-between align-items-center mb-2">
                        <span><i class="fas fa-check-circle text-success me-2"></i>File Tersedia — dokumen sudah diupload.</span>
                        <a href="{{ storage_url($document->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill"><i class="fas fa-eye me-1"></i>Preview</a>
                    </div>
                @endif
                <input type="file" name="file_path" class="form-control">
                <div class="form-text">Upload file baru untuk mengganti yang lama. Max 10MB.</div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">Link Halaman/Section (opsional)</label>
                <input type="text" name="external_url" class="form-control" value="{{ old('external_url', $document->external_url) }}" placeholder="mis. /kontak atau /profil/tugas-fungsi">
                <div class="form-text text-muted">Isi jika info ini sudah punya halaman/section sendiri di web (mis. Alamat Kantor &rarr; halaman Kontak) — publik akan diarahkan ke sana, tidak perlu upload file lagi.</div>
            </div>

            <div class="form-check form-switch mb-4">
                <input class="form-check-input" type="checkbox" name="is_published" value="1" {{ old('is_published', $document->is_published) ? 'checked' : '' }}>
                <label class="form-check-label fw-bold">Published (tampil di halaman publik)</label>
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="{{ route($backRoute) }}" class="btn btn-light me-md-2"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
                <button type="submit" class="btn btn-primary px-5"><i class="fas fa-save me-1"></i> Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
<script>
    $(document).ready(function() {
        $('#description').summernote({
            placeholder: 'Tulis deskripsi di sini...',
            tabsize: 2,
            height: 350,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'video']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ]
        });
    });
</script>
@endpush
