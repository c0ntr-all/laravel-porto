<?php declare(strict_types=1);

namespace App\Containers\AppSection\Authentication\UI\Actions;

use App\Containers\AppSection\Authentication\UI\API\Requests\LoginRequest;
use Illuminate\Support\Facades\Http;
use Lorisleiva\Actions\Concerns\AsAction;

class LoginAction
{
    use AsAction;

    public function handle()
    {
        // ...
    }

    public function asController(LoginRequest $request)
    {
        $credentials = $request->validated();


        $response = Http::asForm()->post(config('api.url') . '/v1/oauth/token', [
            'grant_type' => 'password',
            'client_id' => env('PASSPORT_CLIENT_ID'),
            'client_secret' => env('PASSPORT_CLIENT_SECRET'),
            'username' => $credentials['email'],
            'password' => $credentials['password'],
            'scope' => '',
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        if ($response->status() === 400) {
            $decoded = json_decode($response->body(), true);
            return response()->json($decoded, 400);
        }

        return response()->json(['message' => 'Authentication failed'], 401);
    }
}
