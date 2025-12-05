<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('lifelog_posts', function (Blueprint $table) {
            $table->date('date')->nullable()->after('datetime');
            $table->time('time')->nullable()->after('date');
        });

        DB::transaction(function () {
            DB::table('lifelog_posts')
                ->whereRaw("TIME(datetime) != '00:00:00'")
                ->update([
                    'date' => DB::raw("DATE(datetime)"),
                    'time' => DB::raw("TIME(datetime)")
                ]);

            DB::table('lifelog_posts')
                ->whereRaw("TIME(datetime) = '00:00:00'")
                ->update([
                    'date' => DB::raw("DATE(datetime)"),
                    'time' => null
                ]);
        });

        Schema::table('lifelog_posts', function (Blueprint $table) {
            $table->date('date')->nullable(false)->change();
        });

        Schema::table('lifelog_posts', function (Blueprint $table) {
            $table->dropColumn('datetime');
        });
    }

    public function down(): void
    {
        Schema::table('lifelog_posts', function (Blueprint $table) {
            $table->datetime('datetime')->nullable()->after('time');
        });

        DB::transaction(function () {
            DB::table('lifelog_posts')
                ->whereNotNull('time')
                ->update([
                    'datetime' => DB::raw("CONCAT(date, ' ', time)")
                ]);

            DB::table('lifelog_posts')
                ->whereNull('time')
                ->whereNotNull('date')
                ->update([
                    'datetime' => DB::raw("CONCAT(date, ' 00:00:00')")
                ]);
        });

        Schema::table('lifelog_posts', function (Blueprint $table) {
            $table->dropColumn(['date', 'time']);
        });
    }
};
