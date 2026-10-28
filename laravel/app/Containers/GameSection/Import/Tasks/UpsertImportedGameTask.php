<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Import\Tasks;

use App\Containers\GameSection\Game\Data\Repositories\GameRepository;
use App\Containers\GameSection\Game\Models\Game;
use App\Containers\GameSection\Import\Data\DTO\ParsedSteamGameDto;
use App\Ship\Parents\Tasks\Task as ParentTask;

class UpsertImportedGameTask extends ParentTask
{
    public function __construct(
        private readonly GameRepository $gameRepository,
    ) {}

    public function run(ParsedSteamGameDto $dto): Game
    {
        return $this->gameRepository->upsertBySteamId(
            steamId: $dto->steam_id,
            name: $dto->name,
            description: $dto->description,
            img: $dto->img,
            airDate: $dto->air_date,
            developer: $dto->developer,
            publisher: $dto->publisher,
        );
    }
}
