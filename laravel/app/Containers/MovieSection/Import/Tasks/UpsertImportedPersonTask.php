<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Import\Tasks;

use App\Containers\MovieSection\Import\Data\DTO\ParsedPersonDto;
use App\Containers\MovieSection\Person\Data\DTO\PersonCreateData;
use App\Containers\MovieSection\Person\Data\DTO\PersonUpdateData;
use App\Containers\MovieSection\Person\Data\Repositories\PersonRepository;
use App\Containers\MovieSection\Person\Models\Person;
use App\Containers\MovieSection\Profession\Tasks\FindOrCreateProfessionByEnNameTask;
use App\Ship\Parents\Tasks\Task as ParentTask;

class UpsertImportedPersonTask extends ParentTask
{
    public function __construct(
        private readonly PersonRepository $personRepository,
        private readonly FindOrCreateProfessionByEnNameTask $findOrCreateProfessionByEnNameTask,
    ) {
    }

    /**
     * @return array{person: Person, profession_id: int}
     */
    public function run(ParsedPersonDto $dto): array
    {
        $profession = $this->findOrCreateProfessionByEnNameTask->run($dto->en_profession, $dto->profession);
        $existing = $this->personRepository->findByKpId($dto->kp_id, forUpdate: true);

        if ($existing === null) {
            $person = $this->personRepository->create(PersonCreateData::from([
                'kp_id' => $dto->kp_id,
                'profession_id' => $profession->id,
                'name' => $dto->name,
                'en_name' => $dto->en_name,
                'photo' => $dto->photo,
            ]));

            return ['person' => $person, 'profession_id' => (int) $profession->id];
        }

        $update = [
            'name' => $dto->name,
        ];
        if ($dto->en_name !== null && $dto->en_name !== '') {
            $update['en_name'] = $dto->en_name;
        }
        if ($dto->photo !== null && $dto->photo !== '') {
            $update['photo'] = $dto->photo;
        }
        if ($existing->profession_id === null) {
            $update['profession_id'] = $profession->id;
        }

        $person = $this->personRepository->update($existing, PersonUpdateData::from($update));
        $person->professions()->syncWithoutDetaching([(int) $profession->id]);

        return ['person' => $person, 'profession_id' => (int) $profession->id];
    }
}
