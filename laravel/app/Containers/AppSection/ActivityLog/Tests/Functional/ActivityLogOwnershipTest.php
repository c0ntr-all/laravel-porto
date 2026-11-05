<?php declare(strict_types=1);

namespace App\Containers\AppSection\ActivityLog\Tests\Functional;

use App\Containers\AppSection\ActivityLog\Models\ActivitySystemLog;
use App\Containers\AppSection\ActivityLog\Models\ActivityUseCaseLog;
use App\Containers\AppSection\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ActivityLogOwnershipTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_are_limited_to_the_current_user(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $uuid = (string) Str::uuid();

        ActivityUseCaseLog::create([
            'user_id' => $other->id,
            'correlation_uuid' => (string) Str::uuid(),
            'loggable_type' => 'tm_tasks',
            'loggable_id' => '1',
            'event_type' => 'created',
        ]);
        $own = ActivityUseCaseLog::create([
            'user_id' => $user->id,
            'correlation_uuid' => (string) Str::uuid(),
            'loggable_type' => 'tm_tasks',
            'loggable_id' => '2',
            'event_type' => 'created',
        ]);
        ActivitySystemLog::create([
            'user_id' => null,
            'correlation_uuid' => $uuid,
            'event_type' => 'created',
            'main_type' => 'tm_tasks',
            'main_id' => '1',
        ]);
        ActivitySystemLog::create([
            'user_id' => $other->id,
            'correlation_uuid' => $uuid,
            'event_type' => 'created',
            'main_type' => 'tm_tasks',
            'main_id' => '1',
        ]);

        $this->actingAs($user, 'api')
            ->getJson('/api/v1/use-case-logs')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', (string) $own->id);

        $this->actingAs($user, 'api')
            ->getJson('/api/v1/system-logs?correlation_uuid=' . $uuid)
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }
};
