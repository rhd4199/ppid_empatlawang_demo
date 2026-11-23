@if ($errors->any())
    <div class="alert alert-danger">Periksa kembali isian yang ditandai merah.</div>
@endif

@php
    $C = \App\Http\Controllers\Admin\AgencyController::class;
    $text = fn ($name, $label, $max, $placeholder = '', $required = false) => compact('name', 'label', 'max', 'placeholder', 'required');
    $main = [
        $text('name', 'Nama Resmi', 255, 'Contoh: Dinas Kesehatan Kabupaten Empat Lawang', true),
        $text('short_name', 'Singkatan', 50, 'Contoh: Dinkes'),
        $text('address', 'Alamat Kantor', 500, 'Jalan, kelurahan/desa, kecamatan, kode pos'),
        $text('website', 'Situs Web', 255, 'https://dinkes.empatlawangkab.go.id'),
        $text('service_hours', 'Jam Layanan', 255, 'Senin–Kamis 08.00–16.00 WIB, Jumat 08.00–16.30 WIB'),
        $text('keywords', 'Kata Kunci Sapa Madani', 500, 'Contoh: ktp, akta, kk (dipakai mencocokkan pertanyaan warga)'),
    ];
@endphp

<div class="row">
    <div class="col-lg-8">
        @foreach ($main as $f)
            <div class="mb-3">
                <label for="{{ $f['name'] }}" class="form-label fw-bold">{{ $f['label'] }} @if ($f['required'])<span class="text-danger">*</span>@endif</label>
                <input type="{{ $f['name'] === 'website' ? 'url' : 'text' }}" class="form-control @error($f['name']) is-invalid @enderror" id="{{ $f['name'] }}" name="{{ $f['name'] }}"
                       value="{{ old($f['name'], $item->{$f['name']}) }}" maxlength="{{ $f['max'] }}" placeholder="{{ $f['placeholder'] }}" {{ $f['required'] ? 'required' : '' }}>
                @error($f['name'])<div class="invalid-feedback">{{ $message }}</div>@enderror
                @if ($f['name'] === 'address')
                    <div class="form-check mt-1">
                        <input class="form-check-input" type="checkbox" id="address_verified" name="address_verified" value="1" {{ old('address_verified', $item->address_verified) ? 'checked' : '' }}>
                        <label class="form-check-label small" for="address_verified">Alamat terverifikasi (tampil di situs). Kosongkan centang bila baru 1 sumber.</label>
                    </div>
                @endif
            </div>
        @endforeach

        <div class="mb-3">
            <label for="description" class="form-label fw-bold">Tugas & Layanan Utama</label>
            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4" maxlength="3000">{{ old('description', $item->description) }}</textarea>
            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="phones_text" class="form-label fw-bold">Telepon / WA</label>
                <textarea class="form-control font-monospace small @error('phones_text') is-invalid @enderror" id="phones_text" name="phones_text" rows="4" placeholder="(0702) 7XXXXX | Kantor">{{ old('phones_text', $C::lines($item->phones, 'number', 'label')) }}</textarea>
                @error('phones_text')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6 mb-3">
                <label for="emails_text" class="form-label fw-bold">Email</label>
                <textarea class="form-control font-monospace small @error('emails_text') is-invalid @enderror" id="emails_text" name="emails_text" rows="4" placeholder="dinkes@empatlawangkab.go.id">{{ old('emails_text', $C::lines($item->emails, 'address')) }}</textarea>
                @error('emails_text')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <small class="text-muted d-block mb-3" style="margin-top: -.5rem;">Satu per baris: <code>Nomor | Keterangan</code>. Awali dengan <code>?</code> bila baru 1 sumber (disimpan, tidak tampil, tidak dipakai Sapa Madani).</small>
        </div>

        <div class="row">
            @foreach (\App\Models\Agency::SOCIAL as $network => $label)
                <div class="col-md-6 mb-3">
                    <label for="social_{{ $network }}" class="form-label fw-bold">{{ $label }}</label>
                    <input type="url" class="form-control @error('social.' . $network) is-invalid @enderror" id="social_{{ $network }}" name="social[{{ $network }}]" value="{{ old('social.' . $network, $item->social[$network] ?? '') }}" maxlength="255" placeholder="https://">
                    @error('social.' . $network)<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            @endforeach
        </div>

        <div class="mb-3">
            <label for="sources_text" class="form-label fw-bold">Sumber</label>
            <textarea class="form-control font-monospace small @error('sources_text') is-invalid @enderror" id="sources_text" name="sources_text" rows="4" placeholder="https://empatlawangkab.go.id/dinas-kesehatan | Halaman SKPD">{{ old('sources_text', $C::lines($item->sources, 'url', 'title')) }}</textarea>
            <small class="text-muted">Satu per baris: <code>URL | Judul</code>.</small>
            @error('sources_text')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card bg-light border-0">
            <div class="card-body">
                <div class="mb-3">
                    <label for="category" class="form-label fw-bold">Kategori <span class="text-danger">*</span></label>
                    <select class="form-select @error('category') is-invalid @enderror" id="category" name="category" required>
                        @foreach (\App\Models\Agency::CATEGORIES as $key => $label)
                            <option value="{{ $key }}" {{ old('category', $item->category) === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="slug" class="form-label fw-bold">Slug (URL)</label>
                    <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" value="{{ old('slug', $item->slug) }}" maxlength="120" placeholder="otomatis dari nama">
                    @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="official_id" class="form-label fw-bold">Pimpinan (dari data Pejabat)</label>
                    <select class="form-select @error('official_id') is-invalid @enderror" id="official_id" name="official_id">
                        <option value="">— tidak ditautkan —</option>
                        @foreach ($officials as $official)
                            <option value="{{ $official->id }}" {{ (string) old('official_id', $item->official_id) === (string) $official->id ? 'selected' : '' }}>{{ $official->name }} — {{ Str::limit($official->position, 60) }}{{ $official->is_published ? '' : ' (tersembunyi)' }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Bila ditautkan, nama tampil hanya selama pejabat itu ditampilkan di Profil Pejabat.</small>
                    @error('official_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="head_name" class="form-label fw-bold">Nama Pimpinan (tanpa tautan)</label>
                    <input type="text" class="form-control @error('head_name') is-invalid @enderror" id="head_name" name="head_name" value="{{ old('head_name', $item->head_name) }}" maxlength="255">
                    @error('head_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="row g-2 mb-3">
                    @foreach (['latitude' => 'Lintang', 'longitude' => 'Bujur'] as $name => $label)
                        <div class="col-6">
                            <label for="{{ $name }}" class="form-label fw-bold">{{ $label }}</label>
                            <input type="text" inputmode="decimal" class="form-control @error($name) is-invalid @enderror" id="{{ $name }}" name="{{ $name }}" value="{{ old($name, $item->{$name}) }}" placeholder="{{ $name === 'latitude' ? '-3.73' : '102.90' }}">
                            @error($name)<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    @endforeach
                    <small class="text-muted">Opsional: titik tepat untuk tombol Google Maps.</small>
                </div>

                <div class="mb-3">
                    <label for="confidence" class="form-label fw-bold">Keyakinan Data</label>
                    <select class="form-select @error('confidence') is-invalid @enderror" id="confidence" name="confidence">
                        <option value="">—</option>
                        @foreach (\App\Models\Agency::CONFIDENCE as $key => $label)
                            <option value="{{ $key }}" {{ old('confidence', $item->confidence) === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-7">
                        <label for="checked_at" class="form-label fw-bold">Tanggal Dicek</label>
                        <input type="date" class="form-control @error('checked_at') is-invalid @enderror" id="checked_at" name="checked_at" value="{{ old('checked_at', $item->checked_at?->format('Y-m-d')) }}" max="{{ now()->format('Y-m-d') }}">
                        @error('checked_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-5">
                        <label for="order" class="form-label fw-bold">Urutan</label>
                        <input type="number" min="0" class="form-control @error('order') is-invalid @enderror" id="order" name="order" value="{{ old('order', $item->order) }}">
                        @error('order')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="is_published" name="is_published" value="1" {{ old('is_published', $item->is_published) ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold" for="is_published">Tampilkan di situs</label>
                </div>
            </div>
        </div>
    </div>
</div>

<button type="submit" class="btn btn-primary mt-3">Simpan</button>
