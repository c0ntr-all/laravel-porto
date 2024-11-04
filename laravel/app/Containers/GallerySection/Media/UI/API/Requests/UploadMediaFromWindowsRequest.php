<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Media\UI\API\Requests;

use App\Containers\GallerySection\Media\Rules\ValidateMediaExistence;
use App\Containers\GallerySection\Media\Rules\ValidateMediaExtension;
use Illuminate\Foundation\Http\FormRequest;

class UploadMediaFromWindowsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $windowsImagesRootFolder = config('app.windows_images_root_folder');

        return [
            'paths' => 'required|array',
            'paths.*' => [
                'required',
                'string',
                'starts_with:' . $windowsImagesRootFolder,
                new ValidateMediaExtension(),
                new ValidateMediaExistence()
            ]
        ];
    }
}
