<?php declare(strict_types=1);

namespace App\Containers\AppSection\Attachment\Tasks;

use App\Containers\AppSection\Document\Data\Repositories\DocumentRepository;
use App\Containers\GallerySection\Image\Data\Repositories\ImageRepository;
use App\Containers\GallerySection\Video\Data\Repositories\VideoRepository;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ValidateFileableOwnershipTask extends ParentTask
{
    public function __construct(
        private readonly ImageRepository $imageRepository,
        private readonly VideoRepository $videoRepository,
        private readonly DocumentRepository $documentRepository,
    ) {
    }

    public function run(string $fileableType, string $fileableId, int $userId): Model
    {
        $canonicalType = ContainerAliasEnum::toCanonicalMorphAlias($fileableType);
        $modelClass = Relation::getMorphedModel($canonicalType);

        if ($modelClass === null) {
            throw new NotFoundHttpException('Unknown fileable type.');
        }

        $model = match ($canonicalType) {
            ContainerAliasEnum::GALLERY_IMAGE->value => $this->imageRepository->findForUser($fileableId, $userId),
            ContainerAliasEnum::GALLERY_VIDEO->value => $this->videoRepository->findForUser($fileableId, $userId),
            ContainerAliasEnum::APP_DOCUMENT->value => $this->documentRepository->findForUser($fileableId, $userId),
            default => null,
        };

        if ($model === null) {
            throw new AccessDeniedHttpException('File does not belong to the current user.');
        }

        return $model;
    }
}
