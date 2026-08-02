<?php declare(strict_types=1);

namespace App\Ship\Tests\Unit;

use App\Ship\Helpers\Correlation;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Tests\TestCase;

class CorrelationTest extends TestCase
{
    protected function tearDown(): void
    {
        Correlation::clear();
        parent::tearDown();
    }

    public function test_init_generates_uuid_when_not_set(): void
    {
        Correlation::init();

        $uuid = Correlation::getUuid();

        $this->assertNotNull($uuid);
        $this->assertTrue(Str::isUuid($uuid));
    }

    public function test_init_does_not_override_existing_uuid(): void
    {
        $existingUuid = (string) Str::uuid();
        Correlation::setUuid($existingUuid);

        Correlation::init();

        $this->assertSame($existingUuid, Correlation::getUuid());
    }

    public function test_resolve_uses_provided_uuid(): void
    {
        $uuid = (string) Str::uuid();

        Correlation::resolve($uuid);

        $this->assertSame($uuid, Correlation::getUuid());
    }

    public function test_resolve_generates_uuid_when_null(): void
    {
        Correlation::resolve(null);

        $this->assertNotNull(Correlation::getUuid());
        $this->assertTrue(Str::isUuid(Correlation::getUuid()));
    }

    public function test_resolve_throws_on_invalid_uuid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid correlation_uuid format.');

        Correlation::resolve('not-a-uuid');
    }

    public function test_clear_removes_uuid_from_container(): void
    {
        Correlation::init();
        Correlation::clear();

        $this->assertFalse(App::has('currentCorrelationId'));
    }
}
