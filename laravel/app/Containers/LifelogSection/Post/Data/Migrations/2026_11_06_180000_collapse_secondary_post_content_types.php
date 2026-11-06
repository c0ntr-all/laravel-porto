<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('lifelog_posts')
            ->where('content_type', 'tv_series')
            ->update(['content_type' => 'movie']);

        DB::table('lifelog_posts')
            ->whereIn('content_type', ['task', 'album', 'note'])
            ->update(['content_type' => 'default']);

        $presets = DB::table('lifelog_presets')->whereNotNull('rules')->get(['id', 'rules']);

        foreach ($presets as $preset) {
            $rules = json_decode((string) $preset->rules, true);
            if (!is_array($rules) || !array_key_exists('content_type', $rules)) {
                continue;
            }

            $types = is_array($rules['content_type'])
                ? $rules['content_type']
                : [$rules['content_type']];
            $mapped = [];

            foreach ($types as $type) {
                $value = (string) $type;
                if ($value === 'tv_series') {
                    $mapped[] = 'movie';
                    continue;
                }

                if (in_array($value, ['task', 'album', 'note'], true)) {
                    continue;
                }

                $mapped[] = $value;
            }

            $mapped = array_values(array_unique($mapped));
            if ($mapped === []) {
                unset($rules['content_type']);
            } else {
                $rules['content_type'] = $mapped;
            }

            DB::table('lifelog_presets')
                ->where('id', $preset->id)
                ->update(['rules' => json_encode($rules, JSON_UNESCAPED_UNICODE)]);
        }
    }

    public function down(): void
    {
        // Removed content types are not restored: subjects stay attached,
        // and the post type stays default or movie.
    }
};
