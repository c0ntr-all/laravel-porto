<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('music_upload_history');

        Schema::create('music_uploads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('artist_id')->nullable()->constrained('music_artists')->nullOnDelete();
            $table->string('artist_name')->nullable();
            $table->string('source_path', 1024);
            $table->string('status', 32);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->unsignedInteger('tracks_found')->default(0);
            $table->unsignedInteger('tracks_created')->default(0);
            $table->unsignedInteger('tracks_updated')->default(0);
            $table->unsignedInteger('tracks_skipped')->default(0);
            $table->unsignedInteger('tracks_failed')->default(0);
            $table->unsignedInteger('albums_created')->default(0);
            $table->unsignedInteger('albums_updated')->default(0);
            $table->unsignedInteger('artists_created')->default(0);
            $table->text('error_message')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index('status');
            $table->index('artist_id');
        });

        Schema::create('music_upload_tracks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('upload_id')->constrained('music_uploads')->cascadeOnDelete();
            $table->foreignId('track_id')->nullable()->constrained('music_tracks')->nullOnDelete();
            $table->string('album_name')->nullable();
            $table->string('track_name')->nullable();
            $table->string('source_path', 1024);
            $table->string('status', 16);
            $table->json('snapshot')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['upload_id', 'status']);
            $table->index('track_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('music_upload_tracks');
        Schema::dropIfExists('music_uploads');

        Schema::create('music_upload_history', function (Blueprint $table) {
            $table->id();
            $table->json('data');
            $table->timestamps();
        });
    }
};
