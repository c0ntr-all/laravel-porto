<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Import\Tasks;

use App\Containers\GameSection\Import\Data\DTO\ParsedSteamGameDto;
use App\Containers\GameSection\Import\Data\DTO\SteamStorePageDto;
use App\Containers\GameSection\Import\Support\SteamStorePageParser;
use App\Ship\Parents\Tasks\Task as ParentTask;

class ParseSteamStorePageTask extends ParentTask
{
    public function __construct(
        private readonly SteamStorePageParser $parser,
    ) {}

    public function run(SteamStorePageDto $page): ParsedSteamGameDto
    {
        return $this->parser->parse($page->steam_id, $page->html, $page->url);
    }
}
