@forelse($infos as $info)
<tr>
    <td class="ps-4 fw-bold">
        <a href="{{ route('informasi-publik.show', $info->id) }}" class="text-decoration-none">{{ $info->title }}</a>
    </td>
    <td class="text-muted">{{ Str::limit(strip_tags($info->description), 60) }}</td>
    <td><span class="badge bg-light text-dark border"><i class="far fa-calendar-alt me-1"></i> {{ $info->created_at->format('d M Y') }}</span></td>
    <td class="pe-4 text-end text-nowrap">
        <a href="{{ route('informasi-publik.show', $info->id) }}" class="btn btn-sm btn-outline-primary rounded-pill">
            <i class="fas fa-eye me-1"></i> Lihat
        </a>
        @if($info->file_path)
            <a href="{{ route('informasi-publik.download', $info->id) }}" class="btn btn-sm btn-primary rounded-pill ms-1" title="Unduh {{ $info->title }}">
                <i class="fas fa-download me-1"></i> Unduh
            </a>
        @elseif($info->external_url)
            <a href="{{ $info->external_url }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary rounded-pill ms-1" title="Buka berkas asli">
                <i class="fas fa-arrow-up-right-from-square me-1"></i> Berkas
            </a>
        @endif
    </td>
</tr>
@empty
<tr>
    <td colspan="4" class="text-center py-5">
        <div class="text-muted">
            <i class="fas fa-folder-open fa-3x mb-3 text-light"></i>
            <p>Tidak ada informasi yang ditemukan.</p>
        </div>
    </td>
</tr>
@endforelse
@if($infos->hasPages())
<tr>
    <td colspan="4" class="py-3">
        {{ $infos->links() }}
    </td>
</tr>
@endif
