<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Preset\UI\Actions;

use App\Containers\LifelogSection\Preset\Models\Preset;
use App\Containers\LifelogSection\Preset\Tasks\DeletePresetTask;
use App\Containers\LifelogSection\Preset\UI\API\Requests\DeleteRequest;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Parents\Actions\UseCaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DeletePresetAction extends UseCaseAction
{
    protected ?ContainerAliasEnum $containerAliasEnum = ContainerAliasEnum::LL_PRESET;
    protected ?EventTypesEnum $eventTypesEnum = EventTypesEnum::DELETED;

    public function __construct(
        private readonly DeletePresetTask $deletePresetTask
    )
    {
        parent::__construct();
    }

    public function handle(Preset $preset): ?bool
    {
        return DB::transaction(function () use ($preset) {
            $this->recordUseCase($preset);

            return $this->deletePresetTask->run($preset);
        });
    }

    public function asController(Preset $preset, DeleteRequest $request): JsonResponse
    {
        $this->handle($preset);

        return response()->json([
            'meta' => [
                'message' => 'Preset successfully deleted!',
            ],
        ]);
    }
}
