<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lifelog_post_subjectables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')
                ->constrained('lifelog_posts')
                ->cascadeOnDelete();
            $table->morphs('subjectable');
            $table->timestamps();

            $table->unique(
                ['post_id', 'subjectable_type', 'subjectable_id'],
                'lifelog_post_subjectables_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lifelog_post_subjectables');
    }
};
