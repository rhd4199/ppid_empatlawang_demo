@if ($errors->any())
    <div class="alert alert-danger">Periksa kembali isian yang ditandai merah.</div>
@endif

@php
    $text = fn ($name, $label, $max, $placeholder = '', $required = false) => compact('name', 'label', 'max', 'placeholder', 'required');
    $main = [
        $text('name', 'Nama SPPG', 255, 'Contoh: SPPG Empat Lawang Ulu Musi Muara Kalangan', true),
        $text('kecamatan', 'Kecamatan', 100, 'Contoh: Ulu Musi', true),
        $text('village', 'Desa / Kelurahan', 100, 'Contoh: Muara Kalangan'),
        $text('address', 'Alamat', 500, 'Jalan, desa, kecamatan'),
        $text('operator', 'Mitra / Yayasan Pengelola', 255, 'Contoh: Yayasan ...'),
    ];
    $side = [
        $text('bgn_id', 'ID SPPG (BGN)', 20, 'Contoh: 5ZKMLWVL'),
        $text('code', 'Kode SPPG (BGN)', 30, 'Contoh: 16.11.03.2005.01'),
    ];
    $kecamatanList = \App\Models\SppgUnit::query()->distinct()->orderBy('kecamatan')->pluck('kecamatan');
@endphp

<div class="row">
    <div class="col-lg-8">
        @foreach ($main as $f)
            <div class="mb-3">
                <label for="{{ $f['name'] }}" class="form-label fw-bold">{{ $f['label'] }} @if ($f['required'])<span class="text-danger">*</span>@endif</label>
                <input type="text" class="form-control @error($f['name']) is-invalid @enderror" id="{{ $f['name'] }}" name="{{ $f['name'] }}"
                       value="{{ old($f['name'], $item->{$f['name']}) }}" maxlength="{{ $f['max'] }}" placeholder="{{ $f['placeholder'] }}"
                       {{ $f['required'] ? 'required' : '' }} {!! $f['name'] === 'kecamatan' ? 'list="kecamatanList"' : '' !!}>
                @error($f['name'])<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        @endforeach
        <datalist id="kecamatanList">
            @foreach ($kecamatanList as $kec)<option value="{{ $kec }}">@endforeach
        </datalist>

        <div class="mb-3">
            <label for="institutions_text" class="form-label fw-bold">Instansi yang Dilayani</label>
            <textarea class="form-control font-monospace small @error('institutions_text') is-invalid @enderror" id="institutions_text" name="institutions_text" rows="10"
                      placeholder="SD Negeri 7 Muara Pinang | Batu Galang | Muara Pinang">{{ old('institutions_text', \App\Http\Controllers\Admin\SppgUnitController::institutionsText($item->institutions)) }}</textarea>
            <small class="text-muted">Satu instansi per baris: <code>Nama | Desa | Kecamatan</code> (desa & kecamatan boleh kosong). Jumlah instansi dihitung otomatis.</small>
            @error('institutions_text')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="notes" class="form-label fw-bold">Catatan</label>
            <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="2" maxlength="2000">{{ old('notes', $item->notes) }}</textarea>
            @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card bg-light border-0">
            <div class="card-body">
                <div class="mb-3">
                    <label for="status" class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                        @foreach (\App\Models\SppgUnit::STATUSES as $key => $label)
                            <option value="{{ $key }}" {{ old('status', $item->status) === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="beneficiaries" class="form-label fw-bold">Jumlah Penerima Manfaat</label>
                    <input type="number" min="0" class="form-control @error('beneficiaries') is-invalid @enderror" id="beneficiaries" name="beneficiaries" value="{{ old('beneficiaries', $item->beneficiaries) }}" placeholder="Kosongkan bila belum diketahui">
                    @error('beneficiaries')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="row g-2 mb-3">
                    @foreach (['latitude' => 'Lintang', 'longitude' => 'Bujur'] as $name => $label)
                        <div class="col-6">
                            <label for="{{ $name }}" class="form-label fw-bold">{{ $label }}</label>
                            <input type="text" inputmode="decimal" class="form-control @error($name) is-invalid @enderror" id="{{ $name }}" name="{{ $name }}" value="{{ old($name, $item->{$name}) }}" placeholder="{{ $name === 'latitude' ? '-3.73' : '102.90' }}">
                            @error($name)<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    @endforeach
                    <small class="text-muted">Isi keduanya agar SPPG tampil di peta.</small>
                </div>

                @foreach ($side as $f)
                    <div class="mb-3">
                        <label for="{{ $f['name'] }}" class="form-label fw-bold">{{ $f['label'] }}</label>
                        <input type="text" class="form-control @error($f['name']) is-invalid @enderror" id="{{ $f['name'] }}" name="{{ $f['name'] }}" value="{{ old($f['name'], $item->{$f['name']}) }}" maxlength="{{ $f['max'] }}" placeholder="{{ $f['placeholder'] }}">
                        @error($f['name'])<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                @endforeach

                <div class="mb-3">
                    <label for="source_url" class="form-label fw-bold">Sumber (URL)</label>
                    <input type="url" class="form-control @error('source_url') is-invalid @enderror" id="source_url" name="source_url" value="{{ old('source_url', $item->source_url) }}" maxlength="500" placeholder="https://www.bgn.go.id/radar-mbg/">
                    @error('source_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="checked_at" class="form-label fw-bold">Tanggal Dicek</label>
                    <input type="date" class="form-control @error('checked_at') is-invalid @enderror" id="checked_at" name="checked_at" value="{{ old('checked_at', $item->checked_at?->format('Y-m-d')) }}" max="{{ now()->format('Y-m-d') }}">
                    @error('checked_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
