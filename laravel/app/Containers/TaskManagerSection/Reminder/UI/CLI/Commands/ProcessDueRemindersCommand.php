<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Reminder\UI\CLI\Commands;

use App\Containers\TaskManagerSection\Reminder\Tasks\ProcessDueRemindersTask;
use Illuminate\Console\Command;

class ProcessDueRemindersCommand extends Command
{
    protected $signature = 'reminders:process-due
                            {--limit=100 : Max reminders to process per run}';

    protected $description = 'Find due task reminders and dispatch notification jobs';

    public function handle(ProcessDueRemindersTask $processDueRemindersTask): int
    {
        $limit = max(1, (int) $this->option('limit'));
        $dispatched = $processDueRemindersTask->run(limit: $limit);

        $this->info("Dispatched {$dispatched} reminder notification job(s).");

        return self::SUCCESS;
    }
}
