<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\Tasks;

use App\Containers\MusicSection\Upload\Support\TrackTitleCreditsParser;
use App\Ship\Parents\Tasks\Task as ParentTask;

class ParseTrackTitleTask extends ParentTask
{
    public function __construct(
        private readonly TrackTitleCreditsParser $creditsParser,
    ) {
    }

    /**
     * @return array{name: string, credits: string|null}
     */
    public function run(string $title): array
    {
        return $this->creditsParser->parse($title);
    }
}
