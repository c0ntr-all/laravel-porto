<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lifelog_posts', function (Blueprint $table) {
            $table->string('content_type', 50)
                ->default('default')
                ->after('content');

            $table->index('content_type');
        });
    }

    public function down(): void
    {
        Schema::table('lifelog_posts', function (Blueprint $table) {
            $table->dropIndex(['content_type']);
            $table->dropColumn('content_type');
        });
    }
};
