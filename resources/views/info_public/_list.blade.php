@forelse($infos as $info)
<tr>
    <td class="ps-4 fw-bold">{{ $info->title }}</td>
    <td class="text-muted">{{ Str::limit($info->description, 60) }}</td>
    <td><span class="badge bg-light text-dark border"><i class="far fa-calendar-alt me-1"></i> {{ $info->created_at->format('d M Y') }}</span></td>
    <td class="pe-4 text-end">
        @if($info->file_path)
        <a href="{{ asset('storage/' . $info->file_path) }}" class="btn btn-sm btn-outline-primary rounded-pill" target="_blank">
            <i class="fas fa-download me-1"></i> Download
        </a>
        @else
        <span class="text-muted small">Tidak ada file</span>
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
