<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * OAuth 2 ("modern authentication") for Microsoft 365 and Google.
     *
     * Both providers are retiring SMTP username/password sign-in, so the mail
     * settings need to hold an OAuth client and the refresh token obtained from
     * the consent screen. Access tokens are short lived and refreshed on demand.
     */
    public function up(): void
    {
        Schema::table('mail_settings', function (Blueprint $table) {
            // 'password' (classic SMTP), 'microsoft' or 'google'.
            $table->string('auth_type')->default('password')->after('mailer');

            $table->string('oauth_client_id')->nullable()->after('password');
            // Encrypted at rest via the model's casts.
            $table->text('oauth_client_secret')->nullable()->after('oauth_client_id');
            $table->text('oauth_refresh_token')->nullable()->after('oauth_client_secret');
            $table->text('oauth_access_token')->nullable()->after('oauth_refresh_token');
            $table->timestamp('oauth_expires_at')->nullable()->after('oauth_access_token');
            // The mailbox the tokens belong to; also the SMTP username.
            $table->string('oauth_email')->nullable()->after('oauth_expires_at');
            // Microsoft only: directory to authenticate against.
            $table->string('oauth_tenant')->nullable()->after('oauth_email');
        });
    }

    public function down(): void
    {
        Schema::table('mail_settings', function (Blueprint $table) {
            $table->dropColumn([
                'auth_type',
                'oauth_client_id',
                'oauth_client_secret',
                'oauth_refresh_token',
                'oauth_access_token',
                'oauth_expires_at',
                'oauth_email',
                'oauth_tenant',
            ]);
        });
    }
};
