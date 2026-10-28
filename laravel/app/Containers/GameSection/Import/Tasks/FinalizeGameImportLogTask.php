<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Import\Tasks;

use App\Containers\GameSection\Import\Data\Repositories\GameImportRepository;
use App\Containers\GameSection\Import\Enums\GameImportStatusEnum;
use App\Containers\GameSection\Import\Models\GameImport;
use App\Ship\Parents\Tasks\Task as ParentTask;

class FinalizeGameImportLogTask extends ParentTask
{
    public function __construct(
        private readonly GameImportRepository $gameImportRepository,
    ) {}

    /**
     * @param  array<string, mixed>|null  $parsedPayload
     * @param  array<string, mixed>|null  $meta
     */
    public function run(
        GameImport $import,
        GameImportStatusEnum $status,
        int $startedAtNs,
        ?int $gameId = null,
        ?bool $wasCreated = null,
        ?int $httpStatus = null,
        ?array $parsedPayload = null,
        ?array $meta = null,
        ?string $errorMessage = null,
    ): GameImport {
        $import->status = $status;
        $import->game_id = $gameId ?? $import->game_id;
        $import->was_created = $wasCreated;
        $import->http_status = $httpStatus;
        $import->parsed_payload = $parsedPayload;
        $import->meta = $meta;
        $import->error_message = $errorMessage;
        $import->finished_at = now();
        $import->duration_ms = (int) max(0, intdiv(hrtime(true) - $startedAtNs, 1_000_000));

        return $this->gameImportRepository->save($import);
    }
}
