<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_achievements', function (Blueprint $table) {
            $table->string('icon', 512)->nullable()->after('steam_api_name');
            $table->string('icon_gray', 512)->nullable()->after('icon');
            $table->boolean('hidden')->default(false)->after('icon_gray');
            $table->string('player_percent_unlocked', 16)->nullable()->after('hidden');
            $table->unsignedInteger('steam_internal_key')->nullable()->after('player_percent_unlocked');
            $table->unsignedInteger('min_progress')->nullable()->after('steam_internal_key');
            $table->unsignedInteger('max_progress')->nullable()->after('min_progress');
            $table->unsignedInteger('group_id')->nullable()->after('max_progress');
            $table->boolean('archived')->default(false)->after('group_id');
            $table->unsignedTinyInteger('progress_type')->nullable()->after('archived');
        });
    }

    public function down(): void
    {
        Schema::table('game_achievements', function (Blueprint $table) {
            $table->dropColumn([
                'icon',
                'icon_gray',
                'hidden',
                'player_percent_unlocked',
                'steam_internal_key',
                'min_progress',
                'max_progress',
                'group_id',
                'archived',
                'progress_type',
            ]);
        });
    }
};
