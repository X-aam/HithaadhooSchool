<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database with the site's real starting content.
     */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            SiteContentSeeder::class,
        ]);

        // Demo news, announcements and calendar events are not part of the
        // site's real content. Load them on purpose when wanted:
        //   php artisan db:seed --class=CmsContentSeeder
    }
}
