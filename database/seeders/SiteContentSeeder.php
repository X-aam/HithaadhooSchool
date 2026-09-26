<?php

namespace Database\Seeders;

use App\Models\SiteContent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class SiteContentSeeder extends Seeder
{
    /**
     * Sections seeded from resources/js/data/defaults/{key}.json. The front end
     * falls back to the same files (sampleData.ts), so a fresh install and a
     * section reset in the CMS both show exactly this content.
     */
    private const SECTIONS = ['school', 'hero', 'staff', 'downloads'];

    /**
     * Seed the edited site content and the photos it points at. Sections that
     * already exist are left alone, so this never clobbers CMS edits.
     */
    public function run(): void
    {
        $this->copyUploads();

        foreach (self::SECTIONS as $key) {
            if (SiteContent::query()->where('key', $key)->exists()) {
                continue;
            }

            $value = json_decode(
                File::get(resource_path("js/data/defaults/{$key}.json")),
                true,
                flags: JSON_THROW_ON_ERROR,
            );

            SiteContent::create(['key' => $key, 'value' => $value]);
        }
    }

    /**
     * Uploaded files live on the public disk, which git ignores. The photos the
     * default content references ship in database/seeders/data/uploads and are
     * copied across here; files already on the disk are kept.
     */
    private function copyUploads(): void
    {
        $disk = Storage::disk('public');

        foreach (File::files(database_path('seeders/data/uploads')) as $file) {
            $path = 'uploads/'.$file->getFilename();

            if (! $disk->exists($path)) {
                $disk->put($path, File::get($file->getPathname()));
            }
        }
    }
}
