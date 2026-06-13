<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Period\UI\API\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListPeriodsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            //
        ];
    }
}
