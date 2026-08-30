<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tm_reminder_occurrences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('reminder_id')
                  ->constrained('tm_reminders')
                  ->cascadeOnDelete();
            $table->foreignId('task_id')->constrained('tm_tasks');
            $table->string('status', 32);
            $table->timestamp('scheduled_at');
            $table->timestamp('notified_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->boolean('is_overdue')->default(false);
            $table->timestamps();

            $table->index(['reminder_id', 'status']);
            $table->index(['reminder_id', 'scheduled_at']);
        });

        Schema::table('tm_reminders', function (Blueprint $table) {
            $table->timestamp('last_completed_at')->nullable()->after('last_reminded_at');
        });
    }

    public function down(): void
    {
        Schema::table('tm_reminders', function (Blueprint $table) {
            $table->dropColumn('last_completed_at');
        });

        Schema::dropIfExists('tm_reminder_occurrences');
    }
};
