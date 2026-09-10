# Deployment

Notes for deploying to shared hosting, Plesk in particular, where PHP is
hardened and Node may be unavailable.

## 1. Composer

```
composer install --no-dev --optimize-autoloader
```

Laravel's stock `post-autoload-dump` hook runs `@php artisan package:discover`,
which Composer executes as a **child process** via Symfony Process — that needs
`proc_open()`. Plesk ships `proc_open` in `disable_functions`, so the install
fails at the last step with:

```
The Process class relies on proc_open, which is not available on your PHP installation.
```

This repo replaces that hook with `App\Composer\Scripts::discoverPackages`
(see [app/Composer/Scripts.php](app/Composer/Scripts.php)), which rebuilds
`bootstrap/cache/packages.php` **inside Composer's own process**. Nothing is
spawned, so no `proc_open` is needed and the hook never aborts a deploy — if it
cannot boot the app it prints a warning and carries on.

If a host still trips over Composer scripts for another reason:

```
composer install --no-dev --optimize-autoloader --no-scripts
php artisan package:discover
```

`package:discover` run directly does not need `proc_open`; only Composer's
`@php` wrapper does.

## 2. Environment

Copy `.env.example` to `.env` and set at minimum:

| Key | Notes |
| --- | --- |
| `APP_KEY` | `php artisan key:generate` |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | **Must be the real https URL.** |
| `DB_*` | Database connection |

`APP_URL` matters more here than usual. The public disk builds file URLs from it
([config/filesystems.php](config/filesystems.php)), and the upload code compares
against its host to decide whether a link can be made root-relative. A wrong
value is what produces `http://localhost:8000/storage/...` paths saved into
content.

Mail can be left at its defaults — an administrator sets the real SMTP server in
the admin panel under **Settings → Email**, which overrides the `MAIL_*`
variables at runtime.

## 3. Database

```
php artisan migrate --force
```

## 4. The storage symlink

**Required.** Every upload — hero slides, editor images, the file manager,
downloads — is served from `/storage/...`, which only resolves if
`public/storage` points at `storage/app/public`. Without it every image and
document 404s.

```
php artisan storage:link
```

`storage:link` calls `symlink()`, which Plesk also commonly disables. If that
fails, create it from the shell:

```
ln -s ../storage/app/public public/storage
```

Confirm with `ls -l public/storage`.

## 5. Front-end assets

```
npm ci
npm run build
```

The Inertia pages are hashed bundles, so **stale assets mean a stale site even
with correct PHP** — a page whose props changed will fail to render against an
old bundle.

If the server has no Node, build locally and upload `public/build/` alongside
the PHP. Do not skip it.

## 6. Caches

Run after every deploy, and any time `.env` changes:

```
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

For production you may then warm them with `config:cache` and `route:cache`.
Note that `config:cache` freezes `.env`, so re-run it after any change.

## 7. Writable paths

`storage/` and `bootstrap/cache/` must be writable by the web user.

## Checklist

- [ ] `composer install --no-dev --optimize-autoloader` completed
- [ ] `.env` present, `APP_KEY` set, `APP_URL` is the real https URL
- [ ] `php artisan migrate --force`
- [ ] `public/storage` symlink exists
- [ ] `public/build/` is current for this commit
- [ ] Caches cleared
- [ ] `storage/` and `bootstrap/cache/` writable
