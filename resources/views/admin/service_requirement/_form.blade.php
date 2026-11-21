@if ($errors->any())
    <div class="alert alert-danger">Periksa kembali isian yang ditandai merah.</div>
@endif

<div class="row">
    <div class="col-lg-8">
        <div class="mb-3">
            <label for="title" class="form-label fw-bold">Judul Layanan <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $item->title) }}" required maxlength="255" placeholder="Contoh: Ganti Alamat KTP">
            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="requirements" class="form-label fw-bold">Persyaratan <span class="text-danger">*</span></label>
            <textarea class="form-control rich-editor @error('requirements') is-invalid @enderror" id="requirements" name="requirements" rows="6">{{ old('requirements', $item->requirements) }}</textarea>
            @error('requirements')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="steps" class="form-label fw-bold">Alur / Langkah</label>
            <textarea class="form-control rich-editor @error('steps') is-invalid @enderror" id="steps" name="steps" rows="6">{{ old('steps', $item->steps) }}</textarea>
            @error('steps')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="legal_basis" class="form-label fw-bold">Dasar Hukum</label>
            <textarea class="form-control rich-editor @error('legal_basis') is-invalid @enderror" id="legal_basis" name="legal_basis" rows="3">{{ old('legal_basis', $item->legal_basis) }}</textarea>
            @error('legal_basis')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card bg-light border-0">
            <div class="card-body">
                <div class="mb-3">
                    <label for="category" class="form-label fw-bold">Kategori <span class="text-danger">*</span></label>
                    <select class="form-select @error('category') is-invalid @enderror" id="category" name="category" required>
                        <option value="" disabled {{ old('category', $item->category) ? '' : 'selected' }}>Pilih Kategori...</option>
                        @foreach (\App\Models\ServiceRequirement::CATEGORIES as $key => $label)
                            <option value="{{ $key }}" {{ old('category', $item->category) === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="agency" class="form-label fw-bold">Instansi Pelayanan <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('agency') is-invalid @enderror" id="agency" name="agency" list="agencyList" value="{{ old('agency', $item->agency) }}" required maxlength="255" placeholder="Contoh: Disdukcapil">
                    <datalist id="agencyList">
                        @foreach (\App\Models\ServiceRequirement::AGENCIES as $agency)<option value="{{ $agency }}">@endforeach
                    </datalist>
                    @error('agency')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="cost" class="form-label fw-bold">Biaya</label>
                    <textarea class="form-control @error('cost') is-invalid @enderror" id="cost" name="cost" rows="3" maxlength="2000" placeholder="Contoh: Gratis">{{ old('cost', $item->cost) }}</textarea>
                    @error('cost')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="duration" class="form-label fw-bold">Lama Proses</label>
                    <textarea class="form-control @error('duration') is-invalid @enderror" id="duration" name="duration" rows="2" maxlength="2000" placeholder="Contoh: 1 hari kerja">{{ old('duration', $item->duration) }}</textarea>
                    @error('duration')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="keywords" class="form-label fw-bold">Kata Kunci / Sinonim</label>
                    <textarea class="form-control @error('keywords') is-invalid @enderror" id="keywords" name="keywords" rows="2" maxlength="2000" placeholder="Contoh: pindah alamat, ktp el, e-ktp">{{ old('keywords', $item->keywords) }}</textarea>
                    <small class="text-muted">Pisahkan dengan koma. Dipakai untuk pencarian.</small>
                    @error('keywords')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="source_url" class="form-label fw-bold">Sumber (URL)</label>
                    <input type="url" class="form-control @error('source_url') is-invalid @enderror" id="source_url" name="source_url" value="{{ old('source_url', $item->source_url) }}" maxlength="500" placeholder="https://...">
                    @error('source_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="checked_at" class="form-label fw-bold">Tanggal Dicek</label>
                    <input type="date" class="form-control @error('checked_at') is-invalid @enderror" id="checked_at" name="checked_at" value="{{ old('checked_at', $item->checked_at?->format('Y-m-d')) }}" max="{{ now()->format('Y-m-d') }}">
                    @error('checked_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="slug" class="form-label fw-bold">Slug</label>
                    <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" value="{{ old('slug', $item->slug) }}" maxlength="255" placeholder="otomatis dari judul">
                    @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="is_published" name="is_published" value="1" {{ old('is_published', $item->is_published) ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold" for="is_published">Publikasikan</label>
                </div>
            </div>
        </div>
    </div>
</div>

<button type="submit" class="btn btn-primary mt-3">Simpan</button>

@push('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/40.0.0/classic/ckeditor.js"></script>
<script>
    document.querySelectorAll('.rich-editor').forEach(function (el) {
        ClassicEditor.create(el, {
            toolbar: ['heading', '|', 'bold', 'italic', 'link', '|', 'bulletedList', 'numberedList', '|', 'outdent', 'indent', '|', 'insertTable', 'undo', 'redo']
        }).catch(console.error);
    });
</script>
@endpush
