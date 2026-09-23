<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('lifelog_post_subjectables')) {
            return;
        }

        Schema::table('lifelog_post_subjectables', function (Blueprint $table) {
            if (!Schema::hasColumn('lifelog_post_subjectables', 'payload')) {
                $table->json('payload')->nullable()->after('subjectable_id');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('lifelog_post_subjectables')) {
            return;
        }

        Schema::table('lifelog_post_subjectables', function (Blueprint $table) {
            if (Schema::hasColumn('lifelog_post_subjectables', 'payload')) {
                $table->dropColumn('payload');
            }
        });
    }
};
