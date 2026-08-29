<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('music_uploads', function (Blueprint $table) {
            $table->dropConstrainedForeignId('artist_id');
            $table->dropColumn('artist_name');
        });
    }

    public function down(): void
    {
        Schema::table('music_uploads', function (Blueprint $table) {
            $table->foreignId('artist_id')
                  ->nullable()
                  ->after('user_id')
                  ->constrained('music_artists')
                  ->nullOnDelete();
            $table->string('artist_name')->nullable()->after('artist_id');
        });
    }
};
