<?php

namespace App\Providers;

use App\Models\MailSetting;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureGates();
        $this->configureMailFromSettings();
    }

    /**
     * Apply the mail server settings saved in the admin panel over the MAIL_*
     * environment defaults.
     *
     * The active row is cached, so a normal request does not query for it. Any
     * failure — the table missing before migrations run, an undecryptable
     * password after an APP_KEY change — leaves the environment configuration
     * in place rather than breaking every request in the app.
     */
    protected function configureMailFromSettings(): void
    {
        try {
            if (! Schema::hasTable('mail_settings')) {
                return;
            }

            $settings = MailSetting::active();
        } catch (Throwable) {
            return;
        }

        if ($settings && $settings->isUsable()) {
            config($settings->toConfig());
        }
    }

    /**
     * Role-based access gates for the CMS.
     */
    protected function configureGates(): void
    {
        Gate::define('manage-users', fn (User $user): bool => $user->role->canManageUsers());
        Gate::define('manage-content', fn (User $user): bool => $user->role->canManageContent());
        Gate::define('manage-news', fn (User $user): bool => $user->role->canManageNews());
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
