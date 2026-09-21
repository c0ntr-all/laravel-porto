<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Import\Tasks;

use App\Containers\MovieSection\Import\Data\DTO\ParsedPersonDto;
use App\Ship\Parents\Tasks\Task as ParentTask;

class FindOrCreatePersonsFromParsedTask extends ParentTask
{
    public function __construct(
        private readonly UpsertImportedPersonTask $upsertImportedPersonTask,
    ) {
    }

    /**
     * @param list<ParsedPersonDto> $persons
     * @return list<array{person_id: int, profession_id: int, description: ?string}>
     */
    public function run(array $persons): array
    {
        $rows = [];
        $seen = [];

        foreach ($persons as $person) {
            $upserted = $this->upsertImportedPersonTask->run($person);
            $key = $upserted['person']->id.':'.$upserted['profession_id'];
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $rows[] = [
                'person_id' => (int) $upserted['person']->id,
                'profession_id' => $upserted['profession_id'],
                'description' => $person->description,
            ];
        }

        return $rows;
    }
}
