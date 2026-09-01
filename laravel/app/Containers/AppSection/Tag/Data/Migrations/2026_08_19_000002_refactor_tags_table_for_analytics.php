<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tags')) {
            return;
        }

        $this->addAnalyticsColumns();
        $this->migrateContentToDescription();
        $this->addUserIdColumn();
        $this->dropLegacyUniqueIndexes();
        $this->addCompositeUniqueIndexes();
    }

    public function down(): void
    {
        if (!Schema::hasTable('tags')) {
            return;
        }

        if (Schema::hasColumn('tags', 'user_id')) {
            Schema::table('tags', function (Blueprint $table) {
                $table->dropUnique(['user_id', 'name']);
                $table->dropUnique(['user_id', 'slug']);
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            });
        }

        Schema::table('tags', function (Blueprint $table) {
            if (Schema::hasColumn('tags', 'parent_id')) {
                $table->dropForeign(['parent_id']);
                $table->dropColumn('parent_id');
            }

            if (Schema::hasColumn('tags', 'description')) {
                $table->dropColumn('description');
            }

            if (Schema::hasColumn('tags', 'color')) {
                $table->dropColumn('color');
            }

            if (Schema::hasColumn('tags', 'icon')) {
                $table->dropColumn('icon');
            }
        });
    }

    private function addAnalyticsColumns(): void
    {
        Schema::table('tags', function (Blueprint $table) {
            if (!Schema::hasColumn('tags', 'icon')) {
                $table->string('icon')->nullable()->after('slug');
            }

            if (!Schema::hasColumn('tags', 'color')) {
                $table->string('color')->nullable()->after('icon');
            }

            if (!Schema::hasColumn('tags', 'description')) {
                $table->text('description')->nullable()->after('color');
            }

            if (!Schema::hasColumn('tags', 'parent_id')) {
                $table->foreignId('parent_id')->nullable()->after('description')->constrained('tags');
            }
        });
    }

    private function migrateContentToDescription(): void
    {
        if (!Schema::hasColumn('tags', 'content')) {
            return;
        }

        if (Schema::hasColumn('tags', 'description')) {
            DB::table('tags')
                ->whereNotNull('content')
                ->whereNull('description')
                ->update(['description' => DB::raw('content')]);
        }

        Schema::table('tags', function (Blueprint $table) {
            $table->dropColumn('content');
        });
    }

    private function addUserIdColumn(): void
    {
        if (Schema::hasColumn('tags', 'user_id')) {
            return;
        }

        Schema::table('tags', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained('users');
        });
    }

    private function dropLegacyUniqueIndexes(): void
    {
        $indexes = collect(Schema::getIndexes('tags'))->pluck('name');

        Schema::table('tags', function (Blueprint $table) use ($indexes) {
            if ($indexes->contains('tags_name_unique')) {
                $table->dropUnique(['name']);
            }

            if ($indexes->contains('tags_slug_unique')) {
                $table->dropUnique(['slug']);
            }
        });
    }

    private function addCompositeUniqueIndexes(): void
    {
        if (!Schema::hasColumn('tags', 'user_id')) {
            return;
        }

        $indexes = collect(Schema::getIndexes('tags'))->pluck('name');

        Schema::table('tags', function (Blueprint $table) use ($indexes) {
            if (!$indexes->contains('tags_user_id_name_unique')) {
                $table->unique(['user_id', 'name']);
            }

            if (!$indexes->contains('tags_user_id_slug_unique')) {
                $table->unique(['user_id', 'slug']);
            }

            if (!$indexes->contains('tags_user_id_index')) {
                $table->index('user_id');
            }
        });
    }
};
