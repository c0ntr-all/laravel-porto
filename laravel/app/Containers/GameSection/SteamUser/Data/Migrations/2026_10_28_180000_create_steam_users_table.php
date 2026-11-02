<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('steam_users', function (Blueprint $table) {
            $table->foreignId('user_id')->primary()->constrained('users')->cascadeOnDelete();
            $table->string('steam_id', 32)->unique();
            $table->unsignedTinyInteger('community_visibility_state')->default(0);
            $table->unsignedTinyInteger('profile_state')->nullable();
            $table->string('persona_name');
            $table->unsignedTinyInteger('comment_permission')->nullable();
            $table->string('profile_url', 512)->nullable();
            $table->string('avatar', 512)->nullable();
            $table->string('avatar_medium', 512)->nullable();
            $table->string('avatar_full', 512)->nullable();
            $table->string('avatar_hash', 64)->nullable();
            $table->timestamp('last_logoff')->nullable();
            $table->unsignedTinyInteger('persona_state')->default(0);
            $table->string('real_name')->nullable();
            $table->string('primary_clan_id', 32)->nullable();
            $table->timestamp('time_created')->nullable();
            $table->unsignedInteger('persona_state_flags')->nullable();
            $table->char('loc_country_code', 2)->nullable();
            $table->string('loc_state_code', 8)->nullable();
            $table->unsignedInteger('loc_city_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('steam_users');
    }
};
