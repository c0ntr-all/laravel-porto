<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('lifelog_presets') || Schema::hasColumn('lifelog_presets', 'rules')) {
            return;
        }

        Schema::table('lifelog_presets', function (Blueprint $table) {
            $table->json('rules')->nullable()->after('icon');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('lifelog_presets') || !Schema::hasColumn('lifelog_presets', 'rules')) {
            return;
        }

        Schema::table('lifelog_presets', function (Blueprint $table) {
            $table->dropColumn('rules');
        });
    }
};
