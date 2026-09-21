<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Folder\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class SystemFolderProtectedException extends HttpException
{
    public function __construct(string $message = 'System folders cannot be modified or deleted.')
    {
        parent::__construct(422, $message);
    }
}
