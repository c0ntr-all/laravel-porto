<?php

namespace App\Containers\Authentication\Actions;

use App\Containers\Authentication\UI\API\Requests\LoginRequest;
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

        $response = Http::asForm()->post(config('api.url') . '/api/v1/oauth/token', [
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

        return response()->json(['message' => 'Unauthorized'], 401);
    }
}
