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
        Schema::create('activity_user_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('correlation_uuid'); // не foreign key, для независимой связи сущностей
//            $table->unsignedBigInteger('user_id');
            $table->morphs('loggable');
            $table->string('event_type');
            $table->text('text');
            $table->json('context')->nullable();
            $table->timestamp('created_at');

            $table->foreign('user_id')
                  ->references('id')
                  ->on('users');
            $table->foreign('log_type_id')
                  ->references('id')
                  ->on('activity_log_types');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_user_logs');
    }
};
