<?php declare(strict_types=1);

namespace App\Containers\AppSection\Authentication\Tasks;

use App\Containers\AppSection\Authentication\Data\DTO\CreatePasswordGrantTokenDto;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class CreatePasswordGrantTokenTask
{
    public function run(CreatePasswordGrantTokenDto $dto): Response
    {
        $clientId = config('passport.personal_access_client.id');
        $clientSecret = config('passport.personal_access_client.secret');

        return Http::asForm()->post(config('api.url') . '/v1/oauth/token', [
            'grant_type' => 'password',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'username' => $dto->email,
            'password' => $dto->password,
            'scope' => '',
        ]);
    }
}
