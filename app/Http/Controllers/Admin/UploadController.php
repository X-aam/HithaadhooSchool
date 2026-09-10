<?php

namespace App\Http\Controllers\Admin;

use App\Concerns\ResolvesPublicDiskUrls;
use App\Concerns\StoresPublicUploads;
use App\Http\Controllers\Controller;
use App\Http\Requests\FileUploadRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    use ResolvesPublicDiskUrls;
    use StoresPublicUploads;

    /** Directory documents uploaded from a content editor land in. */
    private const FILE_DIRECTORY = 'files';

    /**
     * Store an uploaded image and return its root-relative public path. Used by
     * the rich text editor (inline images) and the featured-image picker.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,jpg,png,webp,gif', 'max:5120'],
        ]);

        $path = $request->file('image')->store('uploads', 'public');

        return response()->json([
            'url' => $this->relativeUrl(Storage::disk('public')->url($path)),
        ]);
    }

    /**
     * Store an uploaded document and describe it back to the caller, so a
     * content editor can attach a file without a round trip through the file
     * manager to copy its link. The extra fields let the editor fill in the
     * type and size fields on its own.
     */
    public function storeFile(FileUploadRequest $request): JsonResponse
    {
        $file = $request->file('file');
        $size = (int) $file->getSize();
        $path = $this->storeWithReadableName($file, self::FILE_DIRECTORY);

        return response()->json([
            'url' => $this->relativeUrl(Storage::disk('public')->url($path)),
            'name' => pathinfo($path, PATHINFO_FILENAME),
            'extension' => strtolower(pathinfo($path, PATHINFO_EXTENSION)),
            'size' => $this->formatBytes($size),
        ]);
    }
}
