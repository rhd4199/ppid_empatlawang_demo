{{-- Album type: photo album, or a YouTube video (cover optional → YouTube thumbnail). $gallery is null on create. --}}
@php($currentType = old('type', $gallery->type ?? 'photo'))
<div class="mb-3">
    <label class="form-label d-block">Tipe Album</label>
    <div class="btn-group" role="group" aria-label="Tipe album">
        <input type="radio" class="btn-check" name="type" id="type_photo" value="photo" {{ $currentType !== 'video' ? 'checked' : '' }} onchange="toggleGalleryType()">
        <label class="btn btn-outline-primary" for="type_photo"><i class="fas fa-camera me-1"></i> Foto</label>
        <input type="radio" class="btn-check" name="type" id="type_video" value="video" {{ $currentType === 'video' ? 'checked' : '' }} onchange="toggleGalleryType()">
        <label class="btn btn-outline-danger" for="type_video"><i class="fab fa-youtube me-1"></i> Video YouTube</label>
    </div>
</div>

<div class="mb-3" id="video_url_group" {{ $currentType === 'video' ? '' : 'hidden' }}>
    <label for="video_url" class="form-label">Link Video YouTube <span class="text-danger">*</span></label>
    <input type="url" class="form-control @error('url') is-invalid @enderror" id="video_url" name="url" value="{{ old('url', $gallery->url ?? '') }}" maxlength="500"
           placeholder="https://www.youtube.com/watch?v=… atau https://youtu.be/…">
    @error('url')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <small class="text-muted">Sampul boleh dikosongkan: thumbnail YouTube dipakai otomatis.</small>
</div>

<script>
function toggleGalleryType() {
    var video = document.getElementById('type_video').checked;
    document.getElementById('video_url_group').hidden = !video;
    document.getElementById('video_url').required = video;
    var cover = document.getElementById('cover_image');
    if (cover && cover.dataset.requiredForPhoto) cover.required = !video;
}
document.addEventListener('DOMContentLoaded', toggleGalleryType);
</script>
