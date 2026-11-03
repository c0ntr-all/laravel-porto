<?php

declare(strict_types=1);

use App\Containers\NotesSection\Note\Support\NotePlainText;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->longText('content_raw')->nullable()->after('content');
            $table->unsignedBigInteger('parent_id')->nullable()->after('user_id');
            $table->unsignedInteger('pin_position')->nullable()->after('content_raw');

            $table->foreign('parent_id')
                ->references('id')
                ->on('notes')
                ->nullOnDelete();

            $table->index(['user_id', 'parent_id']);
            $table->index(['user_id', 'pin_position']);
        });

        DB::statement('ALTER TABLE notes MODIFY created_at TIMESTAMP(6) NULL');
        DB::statement('ALTER TABLE notes MODIFY updated_at TIMESTAMP(6) NULL');
        DB::statement('ALTER TABLE notes MODIFY deleted_at TIMESTAMP(6) NULL');

        DB::table('notes')
            ->select(['id', 'content'])
            ->orderBy('id')
            ->chunkById(200, function ($rows): void {
                foreach ($rows as $row) {
                    DB::table('notes')->where('id', $row->id)->update([
                        'content_raw' => NotePlainText::fromHtml($row->content),
                    ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropIndex(['user_id', 'parent_id']);
            $table->dropIndex(['user_id', 'pin_position']);
            $table->dropColumn(['content_raw', 'parent_id', 'pin_position']);
        });
    }
};
