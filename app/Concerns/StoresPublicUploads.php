<?php

namespace App\Concerns;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait StoresPublicUploads
{
    /**
     * Store a file on the public disk under a slugged version of its original
     * name, adding a numeric suffix rather than overwriting a name already in
     * use. Returns the stored path relative to the disk root.
     *
     * Keeping the readable name matters for documents: visitors see it in the
     * download URL, unlike editor images where a random hash is fine.
     */
    protected function storeWithReadableName(UploadedFile $file, string $directory): string
    {
        $disk = Storage::disk('public');

        $extension = strtolower($file->getClientOriginalExtension());
        $base = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $base = $base !== '' ? $base : 'file';

        $name = "{$base}.{$extension}";

        for ($i = 1; $disk->exists($directory.'/'.$name); $i++) {
            $name = "{$base}-{$i}.{$extension}";
        }

        $file->storeAs($directory, $name, 'public');

        return $directory.'/'.$name;
    }

    /** Human-readable size, matching how the Downloads page lists files. */
    protected function formatBytes(int $bytes): string
    {
        if ($bytes >= 1024 * 1024) {
            return round($bytes / (1024 * 1024), 1).' MB';
        }

        if ($bytes >= 1024) {
            return round($bytes / 1024).' KB';
        }

        return $bytes.' B';
    }
}
