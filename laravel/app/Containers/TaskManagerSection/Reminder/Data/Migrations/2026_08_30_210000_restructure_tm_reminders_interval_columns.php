<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tm_reminders', function (Blueprint $table) {
            $table->unsignedInteger('interval_value')->nullable()->after('datetime');
            $table->string('interval_unit', 16)->nullable()->after('interval_value');
            $table->unsignedInteger('to_remind_before_value')->nullable()->after('interval_unit');
            $table->string('to_remind_before_unit', 16)->nullable()->after('to_remind_before_value');
            $table->timestamp('next_remind_at')->nullable()->after('is_active');
            $table->timestamp('last_reminded_at')->nullable()->after('next_remind_at');

            $table->index('next_remind_at');
            $table->unique('task_id');
        });

        $this->migrateLegacyIntervalColumns();

        Schema::table('tm_reminders', function (Blueprint $table) {
            $table->dropColumn(['interval', 'to_remind_before']);
        });
    }

    public function down(): void
    {
        Schema::table('tm_reminders', function (Blueprint $table) {
            $table->string('interval')->nullable()->after('datetime');
            $table->string('to_remind_before')->nullable()->after('interval');
        });

        $rows = DB::table('tm_reminders')->select([
            'id',
            'interval_value',
            'interval_unit',
            'to_remind_before_value',
            'to_remind_before_unit',
        ])->get();

        foreach ($rows as $row) {
            DB::table('tm_reminders')->where('id', $row->id)->update([
                'interval' => $row->interval_value && $row->interval_unit
                    ? "{$row->interval_value} {$row->interval_unit}"
                    : null,
                'to_remind_before' => $row->to_remind_before_value && $row->to_remind_before_unit
                    ? "{$row->to_remind_before_value} {$row->to_remind_before_unit}"
                    : null,
            ]);
        }

        Schema::table('tm_reminders', function (Blueprint $table) {
            $table->dropUnique(['task_id']);
            $table->dropIndex(['next_remind_at']);
            $table->dropColumn([
                'interval_value',
                'interval_unit',
                'to_remind_before_value',
                'to_remind_before_unit',
                'next_remind_at',
                'last_reminded_at',
            ]);
        });
    }

    private function migrateLegacyIntervalColumns(): void
    {
        $rows = DB::table('tm_reminders')->select([
            'id',
            'datetime',
            'interval',
            'to_remind_before',
            'is_active',
        ])->get();

        foreach ($rows as $row) {
            $interval = $this->parseLegacyDuration($row->interval);
            $remindBefore = $this->parseLegacyDuration($row->to_remind_before);

            $nextRemindAt = null;
            if ($row->is_active && $row->datetime) {
                $nextRemindAt = $row->datetime;
                if ($remindBefore !== null) {
                    $nextRemindAt = date(
                        'Y-m-d H:i:s',
                        strtotime("-{$remindBefore['value']} {$remindBefore['unit']}", strtotime($row->datetime))
                    );
                }
            }

            DB::table('tm_reminders')->where('id', $row->id)->update([
                'interval_value' => $interval['value'] ?? null,
                'interval_unit' => $interval['unit'] ?? null,
                'to_remind_before_value' => $remindBefore['value'] ?? null,
                'to_remind_before_unit' => $remindBefore['unit'] ?? null,
                'next_remind_at' => $nextRemindAt,
            ]);
        }
    }

    /**
     * @return array{value: int, unit: string}|null
     */
    private function parseLegacyDuration(?string $value): ?array
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        if (preg_match('/^(\d+)\s+(minute|hour|day|week|month|year)s?$/i', trim($value), $matches) !== 1) {
            return null;
        }

        return [
            'value' => (int) $matches[1],
            'unit' => strtolower(rtrim($matches[2], 's')),
        ];
    }
};
