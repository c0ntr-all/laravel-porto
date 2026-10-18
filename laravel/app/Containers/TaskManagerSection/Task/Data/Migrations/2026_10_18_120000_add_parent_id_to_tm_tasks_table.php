<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tm_tasks', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_id')
                  ->nullable()
                  ->default(null)
                  ->after('task_list_id');

            $table->foreign('parent_id')
                  ->references('id')
                  ->on('tm_tasks')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tm_tasks', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn('parent_id');
        });
    }
};
