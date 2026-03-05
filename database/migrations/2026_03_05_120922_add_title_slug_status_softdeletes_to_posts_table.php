<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add columns only if they don't exist (idempotent for partial-run recovery)
        Schema::table('posts', function (Blueprint $table) {
            if (! Schema::hasColumn('posts', 'title')) {
                $table->string('title')->nullable()->after('id');
            }
            if (! Schema::hasColumn('posts', 'slug')) {
                $table->string('slug')->nullable()->after('title');
            }
            if (! Schema::hasColumn('posts', 'status')) {
                $table->enum('status', ['draft', 'published'])->default('draft')->after('slug');
            }
            if (! Schema::hasColumn('posts', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // Backfill slug for every existing row that has no unique slug yet
        DB::table('posts')->orderBy('id')->each(function ($post) {
            $base = Str::slug($post->title ?: ('post-' . $post->id));
            $slug = $base ?: ('post-' . $post->id);
            $count = 1;

            while (DB::table('posts')->where('slug', $slug)->where('id', '!=', $post->id)->exists()) {
                $slug = $base . '-' . $count++;
            }

            DB::table('posts')->where('id', $post->id)->update(['slug' => $slug]);
        });

        // Make title NOT NULL and add unique index on slug (if not already there)
        Schema::table('posts', function (Blueprint $table) {
            $table->string('title')->nullable(false)->change();
            $table->string('slug')->nullable(false)->change();

            $indexNames = array_column(Schema::getIndexes('posts'), 'name');
            if (! in_array('posts_slug_unique', $indexNames)) {
                $table->unique('slug');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn(['title', 'slug', 'status']);
            $table->dropSoftDeletes();
        });
    }
};
