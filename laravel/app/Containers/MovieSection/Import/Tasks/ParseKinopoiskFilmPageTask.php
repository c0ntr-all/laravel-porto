<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Import\Tasks;

use App\Containers\MovieSection\Import\Data\DTO\KinopoiskPageDto;
use App\Containers\MovieSection\Import\Data\DTO\ParsedKinopoiskFilmDto;
use App\Containers\MovieSection\Import\Support\KinopoiskFilmParser;
use App\Ship\Parents\Tasks\Task as ParentTask;

class ParseKinopoiskFilmPageTask extends ParentTask
{
    public function __construct(
        private readonly KinopoiskFilmParser $kinopoiskFilmParser,
    ) {
    }

    public function run(KinopoiskPageDto $page): ParsedKinopoiskFilmDto
    {
        return $this->kinopoiskFilmParser->parse($page);
    }
}
