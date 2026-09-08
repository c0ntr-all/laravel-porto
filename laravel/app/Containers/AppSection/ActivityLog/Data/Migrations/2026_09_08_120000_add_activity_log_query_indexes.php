<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_use_case_logs', function (Blueprint $table) {
            $table->index(['user_id', 'created_at'], 'activity_use_case_logs_user_created_index');
            $table->index(['loggable_type', 'loggable_id', 'created_at'], 'activity_use_case_logs_loggable_created_index');
        });

        Schema::table('activity_system_logs', function (Blueprint $table) {
            $table->index(['user_id', 'created_at'], 'activity_system_logs_user_created_index');
            $table->index(['main_type', 'main_id', 'created_at'], 'activity_system_logs_main_created_index');
        });
    }

    public function down(): void
    {
        Schema::table('activity_use_case_logs', function (Blueprint $table) {
            $table->dropIndex('activity_use_case_logs_user_created_index');
            $table->dropIndex('activity_use_case_logs_loggable_created_index');
        });

        Schema::table('activity_system_logs', function (Blueprint $table) {
            $table->dropIndex('activity_system_logs_user_created_index');
            $table->dropIndex('activity_system_logs_main_created_index');
        });
    }
};
