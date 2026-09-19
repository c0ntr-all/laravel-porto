<?php declare(strict_types=1);

namespace App\Ship\Migrations\Support;

use App\Ship\Helpers\UuidV7;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class IdentitySchema
{
    public static function isIntegerColumn(string $table, string $column): bool
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
            return false;
        }

        foreach (Schema::getColumns($table) as $meta) {
            if (($meta['name'] ?? null) !== $column) {
                continue;
            }

            $type = strtolower((string) ($meta['type_name'] ?? $meta['type'] ?? ''));

            return str_contains($type, 'int');
        }

        return false;
    }

    /**
     * @param list<array{columns: list<string>, on: string, onDelete?: string}> $foreignKeys
     * @return array<string, int>
     */
    public static function convertUuidPrimaryKey(string $table, array $foreignKeys = [], bool $keepUuid = true): array
    {
        if (!Schema::hasTable($table)) {
            return [];
        }

        if (self::isIntegerColumn($table, 'id')) {
            return self::uuidToIdMap($table);
        }

        foreach ($foreignKeys as $foreignKey) {
            try {
                Schema::table($table, function (Blueprint $blueprint) use ($foreignKey): void {
                    $blueprint->dropForeign($foreignKey['columns']);
                });
            } catch (\Throwable) {
                // Foreign key may already be absent on partially migrated databases.
            }
        }

        if ($keepUuid) {
            if (!Schema::hasColumn($table, 'uuid')) {
                Schema::table($table, function (Blueprint $blueprint): void {
                    $blueprint->uuid('uuid')->nullable();
                });
            }

            foreach (DB::table($table)->orderBy('id')->get() as $row) {
                if (!blank($row->uuid ?? null)) {
                    continue;
                }

                DB::table($table)->where('id', $row->id)->update(['uuid' => $row->id]);
            }
        }

        Schema::table($table, function (Blueprint $blueprint): void {
            $blueprint->unsignedBigInteger('new_id')->nullable();
        });

        $mapping = [];
        $nextId = 1;
        foreach (DB::table($table)->orderBy('created_at')->orderBy('id')->get() as $row) {
            $mapping[(string) $row->id] = $nextId;
            DB::table($table)->where('id', $row->id)->update(['new_id' => $nextId]);
            $nextId++;
        }

        DB::statement("ALTER TABLE `{$table}` DROP PRIMARY KEY");
        Schema::table($table, function (Blueprint $blueprint): void {
            $blueprint->dropColumn('id');
        });
        Schema::table($table, function (Blueprint $blueprint): void {
            $blueprint->renameColumn('new_id', 'id');
        });

        DB::statement("ALTER TABLE `{$table}` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY");

        if ($keepUuid) {
            Schema::table($table, function (Blueprint $blueprint): void {
                $blueprint->uuid('uuid')->nullable(false)->change();
            });

            if (!Schema::hasIndex($table, ['uuid'])) {
                Schema::table($table, function (Blueprint $blueprint): void {
                    $blueprint->unique('uuid');
                });
            }
        }

        foreach ($foreignKeys as $foreignKey) {
            try {
                Schema::table($table, function (Blueprint $blueprint) use ($foreignKey): void {
                    $definition = $blueprint->foreign($foreignKey['columns'])
                        ->references('id')
                        ->on($foreignKey['on']);

                    if (($foreignKey['onDelete'] ?? null) === 'cascade') {
                        $definition->cascadeOnDelete();
                    }
                });
            } catch (\Throwable) {
                // Foreign key may already exist.
            }
        }

        return $mapping;
    }

    /**
     * @param array<string, int> $mapping
     * @param list<string> $types
     */
    public static function remapMorphIds(string $table, string $typeColumn, string $idColumn, array $types, array $mapping): void
    {
        if ($mapping === [] || !Schema::hasTable($table) || !Schema::hasColumn($table, $typeColumn) || !Schema::hasColumn($table, $idColumn)) {
            return;
        }

        foreach (DB::table($table)->whereIn($typeColumn, $types)->get() as $row) {
            $oldId = (string) $row->{$idColumn};
            if (!isset($mapping[$oldId])) {
                continue;
            }

            DB::table($table)->where('id', $row->id)->update([
                $idColumn => (string) $mapping[$oldId],
            ]);
        }
    }

    /**
     * @param array<string, int> $mapping
     */
    public static function convertSavedFromId(string $table, array $mapping): void
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'saved_from_id')) {
            return;
        }

        if (self::isIntegerColumn($table, 'saved_from_id')) {
            if (Schema::hasColumn($table, 'saved_from_id_new')) {
                Schema::table($table, function (Blueprint $blueprint): void {
                    $blueprint->dropColumn('saved_from_id_new');
                });
            }

            return;
        }

        if (!Schema::hasColumn($table, 'saved_from_id_new')) {
            Schema::table($table, function (Blueprint $blueprint): void {
                $blueprint->unsignedBigInteger('saved_from_id_new')->nullable();
            });
        }

        foreach (DB::table($table)->whereNotNull('saved_from_id')->get() as $row) {
            $mapped = $mapping[(string) $row->saved_from_id] ?? null;
            DB::table($table)->where('id', $row->id)->update([
                'saved_from_id_new' => $mapped,
            ]);
        }

        $indexName = "{$table}_user_id_album_id_saved_from_id_index";
        self::dropIndexIfExists($table, $indexName);

        Schema::table($table, function (Blueprint $blueprint): void {
            $blueprint->dropColumn('saved_from_id');
        });
        Schema::table($table, function (Blueprint $blueprint): void {
            $blueprint->renameColumn('saved_from_id_new', 'saved_from_id');
        });

        self::addIndexIfMissing($table, $indexName, ['user_id', 'album_id', 'saved_from_id']);
    }

    private static function dropIndexIfExists(string $table, string $indexName): void
    {
        if (!Schema::hasIndex($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($indexName): void {
            $blueprint->dropIndex($indexName);
        });
    }

    /**
     * @param list<string> $columns
     */
    private static function addIndexIfMissing(string $table, string $indexName, array $columns): void
    {
        if (Schema::hasIndex($table, $indexName) || Schema::hasIndex($table, $columns)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($indexName, $columns): void {
            $blueprint->index($columns, $indexName);
        });
    }

    /**
     * @return array<string, int>
     */
    private static function uuidToIdMap(string $table): array
    {
        if (!Schema::hasColumn($table, 'uuid')) {
            return [];
        }

        $mapping = [];
        foreach (DB::table($table)->get(['id', 'uuid']) as $row) {
            if (blank($row->uuid ?? null)) {
                continue;
            }

            $mapping[(string) $row->uuid] = (int) $row->id;
        }

        return $mapping;
    }

    public static function changeColumnToUnsignedBigInteger(string $table, string $column): void
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column) || self::isIntegerColumn($table, $column)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($column): void {
            $blueprint->unsignedBigInteger($column)->change();
        });
    }

    public static function addUuidV7Column(string $table): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        if (!Schema::hasColumn($table, 'uuid')) {
            Schema::table($table, function (Blueprint $blueprint): void {
                $blueprint->uuid('uuid')->nullable()->after('id');
            });
        }

        foreach (DB::table($table)->whereNull('uuid')->orderBy('id')->get() as $row) {
            DB::table($table)->where('id', $row->id)->update([
                'uuid' => UuidV7::generate(),
            ]);
        }

        Schema::table($table, function (Blueprint $blueprint): void {
            $blueprint->uuid('uuid')->nullable(false)->change();
        });

        if (!Schema::hasIndex($table, ['uuid'])) {
            Schema::table($table, function (Blueprint $blueprint): void {
                $blueprint->unique('uuid');
            });
        }
    }
}
