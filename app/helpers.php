<?php

use Illuminate\Support\Facades\Storage;

if (! function_exists('storage_url')) {
    /**
     * Resolve a public URL for a file stored on the "public" disk.
     * Disk-agnostic replacement for asset('storage/'.$path) so switching
     * disks (local <-> S3) doesn't require touching every view.
     */
    function storage_url(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }
}
