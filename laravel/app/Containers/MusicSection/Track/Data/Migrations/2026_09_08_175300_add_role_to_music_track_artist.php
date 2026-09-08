<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('music_track_artist', function (Blueprint $table) {
            $table->string('role', 20)->default('primary')->after('is_author');
        });

        DB::table('music_track_artist')
            ->where('is_author', 0)
            ->update(['role' => 'featured']);
    }

    public function down(): void
    {
        Schema::table('music_track_artist', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
