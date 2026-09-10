<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Files\LocateFileUsage;
use App\Concerns\ResolvesPublicDiskUrls;
use App\Concerns\StoresPublicUploads;
use App\Http\Controllers\Controller;
use App\Http\Requests\FileUploadRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class FileManagerController extends Controller
{
    use ResolvesPublicDiskUrls;
    use StoresPublicUploads;

    /** Directory uploads land in when browsing the disk root. */
    private const DEFAULT_DIRECTORY = 'files';

    /**
     * Friendly names for the directories the app writes to on its own. They
     * describe where a file came from, not what type it is — any folder can
     * hold any of the allowed file types. Other folders keep their real name.
     */
    private const FOLDER_LABELS = [
        'files' => 'Media library',
        'uploads' => 'Page editor uploads',
    ];

    /**
     * Browse one directory of the public disk. Files uploaded here and images
     * added through the page editors live in different folders, so the manager
     * navigates the disk rather than listing a single hardcoded directory.
     */
    public function index(Request $request): Response
    {
        $disk = Storage::disk('public');
        $path = $this->directory($request->query('path'));

        abort_unless($path === '' || $disk->directoryExists($path), 404);

        $hidden = fn (string $entry) => Str::startsWith(basename($entry), '.');

        return Inertia::render('admin/files/Index', [
            'path' => $path,
            'breadcrumbs' => $this->breadcrumbs($path),
            'directories' => collect($disk->directories($path))
                ->reject($hidden)
                ->map(fn (string $dir) => [
                    'path' => $dir,
                    'name' => basename($dir),
                    'label' => self::FOLDER_LABELS[$dir] ?? basename($dir),
                    'count' => count($disk->files($dir)) + count($disk->directories($dir)),
                ])
                ->sortBy('label', SORT_NATURAL | SORT_FLAG_CASE)
                ->values(),
            'files' => collect($disk->files($path))
                ->reject($hidden)
                ->map(fn (string $file) => [
                    'path' => $file,
                    'name' => basename($file),
                    'extension' => strtolower(pathinfo($file, PATHINFO_EXTENSION)),
                    'url' => $this->relativeUrl($disk->url($file)),
                    'size' => $disk->size($file),
                    'modified' => Carbon::createFromTimestamp($disk->lastModified($file))->toIso8601String(),
                ])
                ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
                ->values(),
        ]);
    }

    /**
     * Report everywhere the given file is referenced. Looked up on demand when
     * a file is selected, rather than for every file in a folder, so browsing
     * never has to read every article body on the site.
     */
    public function usage(Request $request, LocateFileUsage $locator): JsonResponse
    {
        $path = $this->directory($request->query('path'));

        abort_if($path === '', 404);
        abort_unless(Storage::disk('public')->fileExists($path), 404);

        return response()->json(['usages' => $locator->forPath($path)]);
    }

    public function store(FileUploadRequest $request): RedirectResponse
    {
        $directory = $this->directory($request->input('path'));
        $directory = $directory !== '' ? $directory : self::DEFAULT_DIRECTORY;

        $path = $this->storeWithReadableName($request->file('file'), $directory);
        $name = basename($path);

        Inertia::flash('toast', ['type' => 'success', 'message' => "\"{$name}\" uploaded."]);

        return back();
    }

    /**
     * Delete a file anywhere on the public disk. The path arrives from the URL,
     * so every segment is checked before it reaches the filesystem.
     */
    public function destroy(string $path): RedirectResponse
    {
        $path = $this->directory($path);

        $disk = Storage::disk('public');

        abort_if($path === '', 404);
        abort_unless($disk->fileExists($path), 404);

        $disk->delete($path);

        Inertia::flash('toast', ['type' => 'success', 'message' => '"'.basename($path).'" deleted.']);

        return back();
    }

    /**
     * Normalise a caller-supplied disk path, aborting on anything that could
     * point outside the disk root. Returns '' for the root itself.
     */
    private function directory(mixed $path): string
    {
        if (! is_string($path)) {
            return '';
        }

        $path = trim(str_replace('\\', '/', $path), '/');

        if ($path === '') {
            return '';
        }

        $segments = explode('/', $path);

        abort_if(in_array('', $segments, true), 404);
        abort_if(in_array('.', $segments, true) || in_array('..', $segments, true), 404);

        return $path;
    }

    /**
     * Trail from the disk root to the current directory, root first.
     *
     * @return list<array{name: string, path: string}>
     */
    private function breadcrumbs(string $path): array
    {
        $crumbs = [['name' => 'Files', 'path' => '']];
        $walked = '';

        foreach (array_filter(explode('/', $path)) as $segment) {
            $walked = $walked === '' ? $segment : $walked.'/'.$segment;
            $crumbs[] = ['name' => self::FOLDER_LABELS[$walked] ?? $segment, 'path' => $walked];
        }

        return $crumbs;
    }
}
