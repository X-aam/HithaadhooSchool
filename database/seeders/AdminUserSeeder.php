<?php

namespace Database\Seeders;

use App\Enums\TeamRole;
use App\Enums\UserRole;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    /**
     * Create the first CMS administrator, unless an account with that email
     * already exists.
     *
     * The credentials come from SEED_ADMIN_* in .env (config app.seed_admin)
     * rather than from the repo. Without SEED_ADMIN_PASSWORD a random password
     * is generated and printed once; sign in with it and change it.
     */
    public function run(): void
    {
        $email = (string) config('app.seed_admin.email');
        $configuredPassword = (string) config('app.seed_admin.password');

        if (User::query()->where('email', $email)->exists()) {
            $this->command->info("Admin user {$email} already exists; skipped.");

            return;
        }

        $name = (string) config('app.seed_admin.name');
        $password = $configuredPassword !== '' ? $configuredPassword : Str::password(20);

        DB::transaction(function () use ($name, $email, $password) {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'role' => UserRole::Admin,
                'password' => $password,
            ]);

            $user->forceFill(['email_verified_at' => now()])->save();

            // Mirrors CreateTeam. The slug is set here because DatabaseSeeder
            // runs without model events, so Team's slug hook never fires.
            $team = Team::create([
                'name' => "{$name}'s Team",
                'slug' => $this->uniqueSlug("{$name}'s Team"),
                'is_personal' => true,
            ]);

            $team->memberships()->create([
                'user_id' => $user->id,
                'role' => TeamRole::Owner,
            ]);

            $user->switchTeam($team);
        });

        if ($configuredPassword === '') {
            $this->command->warn("Created admin {$email} with password: {$password}");
        } else {
            $this->command->info("Created admin {$email}.");
        }
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;

        for ($i = 2; Team::withTrashed()->where('slug', $slug)->exists(); $i++) {
            $slug = "{$base}-{$i}";
        }

        return $slug;
    }
}
