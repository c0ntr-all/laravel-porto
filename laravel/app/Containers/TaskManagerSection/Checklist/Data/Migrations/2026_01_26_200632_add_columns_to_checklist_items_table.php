<?php

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
        Schema::table('tm_checklist_items', function (Blueprint $table) {
            $table->boolean('is_declined')
                ->after('finished_at')
                ->default(false);
            $table->string('decline_reason')
                ->after('is_declined')
                ->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tm_checklist_items', function (Blueprint $table) {
            $table->dropColumn('is_declined');
            $table->dropColumn('decline_reason');
        });
    }
};
