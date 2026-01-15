<?php declare(strict_types=1);

namespace App\Ship\Parents\Actions;

use App\Ship\Helpers\Correlation;
use Lorisleiva\Actions\Concerns\AsAction;

class BaseAction
{
    use AsAction;

    public function __construct()
    {
        Correlation::init();
    }
}
