<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tm_tasks', function (Blueprint $table) {
            $table->boolean('is_favorite')
                  ->default(false)
                  ->after('is_declined');

            $table->unsignedInteger('priority')
                  ->default(0)
                  ->after('is_favorite');

            $table->index(['task_list_id', 'parent_id', 'priority'], 'tm_tasks_list_parent_priority_index');
        });

        $tasks = DB::table('tm_tasks')
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->get(['id', 'task_list_id', 'parent_id']);

        $counters = [];

        foreach ($tasks as $task) {
            $scopeKey = ($task->parent_id ?? 'root') . ':' . ($task->task_list_id ?? 'inbox');
            $priority = $counters[$scopeKey] ?? 0;
            $counters[$scopeKey] = $priority + 1;

            DB::table('tm_tasks')
                ->where('id', $task->id)
                ->update(['priority' => $priority]);
        }
    }

    public function down(): void
    {
        Schema::table('tm_tasks', function (Blueprint $table) {
            $table->dropIndex('tm_tasks_list_parent_priority_index');
            $table->dropColumn(['is_favorite', 'priority']);
        });
    }
};
