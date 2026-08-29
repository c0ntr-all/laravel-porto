<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('music_tag_groups', function (Blueprint $table) {
            $table->string('slug', 50)->nullable()->after('name');
            $table->text('description')->nullable()->after('slug');
            $table->boolean('is_system')->default(true)->after('description');
            $table->boolean('is_active')->default(true)->after('is_system');
            $table->integer('display_order')->default(0)->after('is_active');
        });

        $this->backfillGroupSlugs();

        Schema::table('music_tag_groups', function (Blueprint $table) {
            $table->unique('slug');
        });

        Schema::table('music_tags', function (Blueprint $table) {
            $table->foreignId('group_id')
                  ->nullable()
                  ->after('parent_id')
                  ->constrained('music_tag_groups')
                  ->nullOnDelete();
        });

        Schema::table('music_tags', function (Blueprint $table) {
            $table->renameColumn('content', 'description');
        });

        Schema::table('music_tags', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('description');
            $table->dropColumn('is_base');
        });

        Schema::create('music_track_tag', function (Blueprint $table) {
            $table->foreignId('track_id')->constrained('music_tracks')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('music_tags')->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['track_id', 'tag_id']);
        });

        $this->copyTrackTagsFromTagables();

        Schema::dropIfExists('music_tagables');

        Schema::create('music_user_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('color', 32)->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'name']);
        });

        Schema::create('music_user_track_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_tag_id')->constrained('music_user_tags')->cascadeOnDelete();
            $table->foreignId('track_id')->constrained('music_tracks')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_tag_id', 'track_id']);
            $table->index(['user_id', 'track_id']);
        });

        Schema::create('music_artist_aggregated_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artist_id')->constrained('music_artists')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('music_tags')->cascadeOnDelete();
            $table->unsignedInteger('tracks_count')->default(0);
            $table->decimal('percentage', 5, 2)->nullable();
            $table->timestamps();

            $table->unique(['artist_id', 'tag_id']);
        });

        Schema::create('music_album_aggregated_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('album_id')->constrained('music_albums')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('music_tags')->cascadeOnDelete();
            $table->unsignedInteger('tracks_count')->default(0);
            $table->decimal('percentage', 5, 2)->nullable();
            $table->timestamps();

            $table->unique(['album_id', 'tag_id']);
        });

        Schema::create('music_user_artist_aggregated_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('artist_id')->constrained('music_artists')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('music_user_tags')->cascadeOnDelete();
            $table->unsignedInteger('tracks_count')->default(0);
            $table->decimal('percentage', 5, 2)->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'artist_id', 'tag_id'], 'user_artist_agg_tags_unique');
        });

        Schema::create('music_user_album_aggregated_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('album_id')->constrained('music_albums')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('music_user_tags')->cascadeOnDelete();
            $table->unsignedInteger('tracks_count')->default(0);
            $table->decimal('percentage', 5, 2)->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'album_id', 'tag_id'], 'user_album_agg_tags_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('music_user_album_aggregated_tags');
        Schema::dropIfExists('music_user_artist_aggregated_tags');
        Schema::dropIfExists('music_album_aggregated_tags');
        Schema::dropIfExists('music_artist_aggregated_tags');
        Schema::dropIfExists('music_user_track_tag');
        Schema::dropIfExists('music_user_tags');

        Schema::create('music_tagables', function (Blueprint $table) {
            $table->unsignedBigInteger('tag_id');
            $table->unsignedBigInteger('tagable_id');
            $table->string('tagable_type');
            $table->unsignedBigInteger('tag_group_id')->nullable();
            $table->timestamps();
            $table->primary(['tag_id', 'tagable_id', 'tagable_type']);
        });

        Schema::dropIfExists('music_track_tag');

        Schema::table('music_tags', function (Blueprint $table) {
            $table->boolean('is_base')->default(true);
            $table->dropColumn('is_active');
        });
        Schema::table('music_tags', function (Blueprint $table) {
            $table->renameColumn('description', 'content');
        });
        Schema::table('music_tags', function (Blueprint $table) {
            $table->dropConstrainedForeignId('group_id');
        });

        Schema::table('music_tag_groups', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn(['slug', 'description', 'is_system', 'is_active', 'display_order']);
        });
    }

    private function backfillGroupSlugs(): void
    {
        $used = [];

        DB::table('music_tag_groups')->orderBy('id')->each(function (object $group) use (&$used) {
            $base = Str::slug((string) $group->name);
            if ($base === '') {
                $base = 'group-' . $group->id;
            }
            $base = substr($base, 0, 50);

            $slug = $base;
            $suffix = 2;
            while (isset($used[$slug])) {
                $slug = $base . '-' . $suffix;
                $suffix++;
            }
            $used[$slug] = true;

            DB::table('music_tag_groups')->where('id', $group->id)->update(['slug' => $slug]);
        });
    }

    private function copyTrackTagsFromTagables(): void
    {
        if (!Schema::hasTable('music_tagables')) {
            return;
        }

        $rows = DB::table('music_tagables')
            ->whereIn('tagable_type', [
                'music_tracks',
                'App\\Containers\\MusicSection\\Track\\Models\\Track',
                'App\\Models\\Music\\Track',
            ])
            ->get(['tag_id', 'tagable_id', 'created_at', 'updated_at']);

        $payload = [];
        foreach ($rows as $row) {
            $payload[] = [
                'track_id' => $row->tagable_id,
                'tag_id' => $row->tag_id,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ];
        }

        foreach (array_chunk($payload, 500) as $chunk) {
            DB::table('music_track_tag')->insertOrIgnore($chunk);
        }
    }
};
