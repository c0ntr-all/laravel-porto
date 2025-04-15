<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tm_tasks', function (Blueprint $table) {
            $table->boolean('is_declined')
                  ->after('finished_at')
                  ->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tm_tasks', function (Blueprint $table) {
            $table->dropColumn('is_declined');
        });
    }
};
