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
        Schema::create('activity_system_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('action_type_id');
            $table->uuid('correlation_uuid'); // не foreign key, для независимой связи сущностей
            $table->timestamp('created_at');

            $table->index('correlation_uuid');
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users');
            $table->foreign('action_type_id')
                  ->references('id')
                  ->on('activity_log_types');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_system_logs');
    }
};
