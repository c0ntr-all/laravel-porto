<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Import\Tasks;

use App\Containers\MovieSection\Import\Data\Repositories\MovieImportRepository;
use App\Containers\MovieSection\Import\Enums\MovieImportStatusEnum;
use App\Containers\MovieSection\Import\Models\MovieImport;
use App\Containers\MovieSection\Movie\Models\Movie;
use App\Ship\Parents\Tasks\Task as ParentTask;

class FinalizeMovieImportLogTask extends ParentTask
{
    public function __construct(
        private readonly MovieImportRepository $movieImportRepository,
    ) {
    }

    /**
     * @param array<string, mixed>|null $parsedPayload
     * @param array<string, mixed>|null $meta
     */
    public function run(
        MovieImport $import,
        MovieImportStatusEnum $status,
        int $startedAtNs,
        ?int $movieId = null,
        ?bool $wasCreated = null,
        ?int $httpStatus = null,
        ?array $parsedPayload = null,
        ?array $meta = null,
        ?string $errorMessage = null,
    ): MovieImport {
        $import->status = $status;
        $import->movie_id = $movieId ?? $import->movie_id;
        $import->was_created = $wasCreated;
        $import->http_status = $httpStatus;
        $import->parsed_payload = $parsedPayload;
        $import->meta = $meta;
        $import->error_message = $errorMessage;
        $import->finished_at = now();
        $import->duration_ms = (int) max(0, intdiv(hrtime(true) - $startedAtNs, 1_000_000));

        $saved = $this->movieImportRepository->save($import);

        if ($status === MovieImportStatusEnum::Completed && $saved->movie_id !== null && $saved->finished_at !== null) {
            Movie::query()->whereKey($saved->movie_id)->update([
                'kp_imported_at' => $saved->finished_at,
            ]);
        }

        return $saved;
    }
}
