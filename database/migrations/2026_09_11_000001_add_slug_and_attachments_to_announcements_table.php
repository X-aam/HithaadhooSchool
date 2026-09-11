<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Announcements gain their own pages, like news articles, so they need a
     * slug to address them by. They also gain file attachments — a circular
     * or a form is the usual reason for posting one.
     */
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('id');
            $table->json('attachments')->nullable()->after('body');
        });

        $this->backfillSlugs();

        Schema::table('announcements', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->unique()->change();
        });
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn(['slug', 'attachments']);
        });
    }

    /**
     * Give existing rows a slug from their English title, keeping it unique by
     * appending the row id when two announcements share a title.
     */
    private function backfillSlugs(): void
    {
        foreach (DB::table('announcements')->select('id', 'title')->get() as $row) {
            $title = json_decode((string) $row->title, true);
            $base = Str::slug($title['en'] ?? '') ?: 'announcement';

            $slug = $base;
            $taken = DB::table('announcements')
                ->where('slug', $slug)
                ->where('id', '!=', $row->id)
                ->exists();

            if ($taken) {
                $slug = $base.'-'.$row->id;
            }

            DB::table('announcements')->where('id', $row->id)->update(['slug' => $slug]);
        }
    }
};
