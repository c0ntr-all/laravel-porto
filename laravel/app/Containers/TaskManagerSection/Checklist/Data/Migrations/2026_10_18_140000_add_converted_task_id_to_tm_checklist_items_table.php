<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tm_checklist_items', function (Blueprint $table) {
            $table->unsignedBigInteger('converted_task_id')
                  ->nullable()
                  ->default(null)
                  ->after('decline_reason');

            $table->foreign('converted_task_id')
                  ->references('id')
                  ->on('tm_tasks')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tm_checklist_items', function (Blueprint $table) {
            $table->dropForeign(['converted_task_id']);
            $table->dropColumn('converted_task_id');
        });
    }
};
