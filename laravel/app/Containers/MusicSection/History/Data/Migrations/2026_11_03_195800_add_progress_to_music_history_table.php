<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('music_history', function (Blueprint $table) {
            $table->decimal('progress', 5, 2)
                  ->nullable()
                  ->after('track_id');
        });
    }

    public function down(): void
    {
        Schema::table('music_history', function (Blueprint $table) {
            $table->dropColumn('progress');
        });
    }
};
