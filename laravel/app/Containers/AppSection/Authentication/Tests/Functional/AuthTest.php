<?php declare(strict_types=1);

namespace App\Containers\AppSection\Authentication\Tests\Functional;

use App\Containers\AppSection\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('secret-password'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'secret-password',
        ]);

        $response->assertOk();
    }

    public function test_user_cannot_login_with_wrong_password(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('correct-password'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(400);
    }

    public function test_user_cannot_login_with_invalid_email(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(400);
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/v1/logout');

        $response->assertOk();
    }

    public function test_unauthenticated_user_cannot_logout(): void
    {
        $response = $this->postJson('/api/v1/logout');

        $response->assertUnauthorized();
    }

    public function test_user_can_get_profile(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
        ]);

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/v1/profile');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['id', 'attributes' => ['name', 'email']],
            ]);
    }

    public function test_unauthenticated_user_cannot_get_profile(): void
    {
        $response = $this->getJson('/api/v1/profile');

        $response->assertUnauthorized();
    }
}
