<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $dashboards = DB::table('dashboards')
            ->select(['id', 'user_id'])
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->get();

        foreach ($dashboards as $dashboard) {
            $sort = (int) DB::table('dashboard_widgets')
                ->where('dashboard_id', $dashboard->id)
                ->max('sort_order');

            foreach (['notes.recent-notes', 'notes.pinned-notes'] as $type) {
                $exists = DB::table('dashboard_widgets')
                    ->where('dashboard_id', $dashboard->id)
                    ->where('type', $type)
                    ->whereNull('deleted_at')
                    ->exists();

                if ($exists) {
                    continue;
                }

                $sort++;
                DB::table('dashboard_widgets')->insert([
                    'dashboard_id' => $dashboard->id,
                    'user_id' => $dashboard->user_id,
                    'type' => $type,
                    'title' => null,
                    'size' => 'third',
                    'sort_order' => $sort,
                    'config' => json_encode(['limit' => 5], JSON_THROW_ON_ERROR),
                    'is_enabled' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('dashboard_widgets')
            ->whereIn('type', ['notes.recent-notes', 'notes.pinned-notes'])
            ->delete();
    }
};
