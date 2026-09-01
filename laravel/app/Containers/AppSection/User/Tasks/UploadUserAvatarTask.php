<?php declare(strict_types=1);

namespace App\Containers\AppSection\User\Tasks;

use App\Containers\AppSection\User\Data\DTO\UploadUserAvatarDto;
use App\Containers\AppSection\User\Data\Repositories\UserRepository;
use App\Containers\AppSection\User\Models\User;
use App\Containers\GallerySection\Image\Enums\ImageMimeEnum;
use App\Ship\Helpers\ImageUpload;
use App\Ship\Parents\Tasks\Task;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;

class UploadUserAvatarTask extends Task
{
    public function __construct(
        private readonly UserRepository $userRepository
    ) {
    }

    public function run(User $user, UploadUserAvatarDto $dto): User
    {
        $extension = ImageMimeEnum::canonicalize($dto->file->getClientOriginalExtension())
            ?? (strtolower((string) $dto->file->getClientOriginalExtension()) ?: 'jpg');
        $folder = 'avatars/' . $user->id;
        $filename = Uuid::uuid4()->toString() . '.' . $extension;
        $previous = $user->avatar;

        $path = ImageUpload::make()
            ->setDiskName('public')
            ->setFolder($folder)
            ->setFilename($filename)
            ->upload($dto->file);

        $user = $this->userRepository->updateAvatar($user, $path);
        $this->deleteStoredAvatar($previous);

        return $user;
    }

    private function deleteStoredAvatar(?string $path): void
    {
        if (!is_string($path) || $path === '' || str_contains($path, 'http')) {
            return;
        }

        Storage::disk('public')->delete($path);
    }
}
