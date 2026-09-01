<?php declare(strict_types=1);

use App\Containers\GallerySection\Image\Enums\ImageMimeEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    public function up(): void
    {
        $disk = Storage::disk((string) config('image.disk', 'public'));
        $masks = (array) config('image.default.mask', []);

        foreach (DB::table('gallery_images')->orderBy('id')->get() as $image) {
            $canonical = ImageMimeEnum::canonicalize((string) $image->extension)
                ?? strtolower((string) $image->extension);

            foreach ($masks as $mask) {
                $from = $this->applyMask((string) $mask, $image, (string) $image->extension);
                $to = $this->applyMask((string) $mask, $image, $canonical);

                if ($from === $to || !$disk->exists($from)) {
                    continue;
                }

                if (!$disk->exists($to)) {
                    $disk->move($from, $to);
                }
            }

            if ($canonical !== $image->extension) {
                DB::table('gallery_images')->where('id', $image->id)->update([
                    'extension' => $canonical,
                ]);
            }
        }
    }

    public function down(): void
    {
    }

    private function applyMask(string $mask, object $image, string $extension): string
    {
        return str_replace(
            ['{user_id}', '{album_id}', '{file_id}', '{ext}'],
            [(string) $image->user_id, (string) $image->album_id, (string) $image->id, $extension],
            $mask,
        );
    }
};
