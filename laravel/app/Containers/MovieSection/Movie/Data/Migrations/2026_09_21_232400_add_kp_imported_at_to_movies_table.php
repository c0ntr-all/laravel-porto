<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->timestamp('kp_imported_at')->nullable()->after('kp_img');
        });

        $lastImports = DB::table('movie_imports')
            ->select('movie_id', DB::raw('MAX(finished_at) as last_at'))
            ->where('status', 'completed')
            ->whereNotNull('movie_id')
            ->whereNotNull('finished_at')
            ->groupBy('movie_id')
            ->get();

        foreach ($lastImports as $row) {
            DB::table('movies')
                ->where('id', $row->movie_id)
                ->update(['kp_imported_at' => $row->last_at]);
        }
    }

    public function down(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->dropColumn('kp_imported_at');
        });
    }
};
