<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Import\Exceptions;

class KinopoiskBlockedException extends KinopoiskImportException
{
    public function __construct(
        string $message = 'Kinopoisk blocked the request with a captcha or SSO challenge.',
        array $context = [],
    ) {
        parent::__construct($message, 503, null, null, $context);
    }
}
