<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attachments', function (Blueprint $table) {
            $table->unique(
                ['attachable_type', 'attachable_id', 'fileable_type', 'fileable_id'],
                'attachments_attachable_fileable_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('attachments', function (Blueprint $table) {
            $table->dropUnique('attachments_attachable_fileable_unique');
        });
    }
};
