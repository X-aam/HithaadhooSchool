<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Outgoing mail server settings, editable from the admin panel so the
     * school's IT contact can change them without a deploy. A single row holds
     * the active configuration; when it is absent the app falls back to the
     * MAIL_* environment variables.
     */
    public function up(): void
    {
        Schema::create('mail_settings', function (Blueprint $table) {
            $table->id();
            $table->string('mailer')->default('smtp');
            $table->string('host')->nullable();
            $table->unsignedInteger('port')->nullable();
            $table->string('encryption')->nullable();
            $table->string('username')->nullable();
            // Encrypted at rest via the model's `encrypted` cast.
            $table->text('password')->nullable();
            $table->string('from_address')->nullable();
            $table->string('from_name')->nullable();
            $table->boolean('verify_peer')->default(true);
            $table->timestamp('last_tested_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_settings');
    }
};
