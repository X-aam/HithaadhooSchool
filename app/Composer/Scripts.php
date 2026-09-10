<?php

namespace App\Composer;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\PackageManifest;
use Throwable;

/**
 * Composer hooks that run inside Composer's own PHP process.
 *
 * Laravel's stock `post-autoload-dump` calls `@php artisan package:discover`,
 * which Composer executes as a child process through Symfony Process — and that
 * needs `proc_open()`. Shared hosts (Plesk in particular) ship `proc_open` in
 * `disable_functions`, so `composer install` dies at that final step even though
 * the install itself succeeded.
 *
 * Rebuilding the package manifest in-process avoids spawning anything.
 */
class Scripts
{
    /**
     * Rebuild bootstrap/cache/packages.php — the same work
     * `php artisan package:discover` does.
     */
    public static function discoverPackages(): void
    {
        try {
            /** @var Application $app */
            $app = require __DIR__.'/../../bootstrap/app.php';

            $app->make(PackageManifest::class)->build();

            echo 'Discovered packages.'.PHP_EOL;
        } catch (Throwable $e) {
            /*
             * Never fail the install over this. A stale manifest is recoverable
             * with `php artisan package:discover`, whereas a non-zero exit here
             * aborts the whole deploy — which is the exact failure this hook was
             * written to avoid.
             */
            echo 'Could not discover packages: '.$e->getMessage().PHP_EOL;
            echo 'Run `php artisan package:discover` once the app can boot.'.PHP_EOL;
        }
    }
}
