<?php declare(strict_types=1);

namespace App\Ship\Parents\Requests;

abstract class AdminRequest extends AuthenticatedRequest
{
    public function authorize(): bool
    {
        return parent::authorize() && $this->user()->hasRole('admin');
    }
}
