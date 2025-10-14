<?php declare(strict_types=1);

namespace App\Containers\AppSection\Attachment\UI\API\Requests;

use App\Ship\Enums\ContainerAliasEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UploadRequest extends FormRequest
{
    const array ALLOWED_CONTAINERS = [
        ContainerAliasEnum::LL_POST->value,
        ContainerAliasEnum::TM_TASK->value,
    ];
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
        $attachableType = request()->input('attachable_type');

        if (!in_array($attachableType, static::ALLOWED_CONTAINERS)) {
            abort(422, 'Disallowed attachable type!');
        }

        $allowedMimes = config('attachments.' . strtolower($attachableType) . '.allowed_mimes')
            ?? config('attachments.default.allowed_mimes');

        $maxSize = config('attachments.' . strtolower($attachableType) . 'max_file_size')
            ?? config('attachments.default.max_file_size');

        return [
            'files' => 'required|array',
            'files.*' => [
                'required',
                'file',
                'max:' . $maxSize,
                'mimetypes:' . implode(',', $allowedMimes),
            ],
            'attachable_type' => ['required', Rule::in(static::ALLOWED_CONTAINERS)],
            'attachable_id' => 'required|string'
        ];
    }
}
