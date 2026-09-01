<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Widget\Tests\Unit;

use App\Containers\DashboardSection\Widget\Enums\WidgetSizeEnum;
use App\Containers\DashboardSection\Widget\Exceptions\InvalidWidgetConfigException;
use App\Containers\DashboardSection\Widget\Tasks\ValidateWidgetConfigTask;
use App\Containers\DashboardSection\Widget\Tests\Fakes\FakeWidget;
use Tests\TestCase;

class ValidateWidgetConfigTaskTest extends TestCase
{
    public function test_it_merges_defaults_and_casts_values(): void
    {
        $task = new ValidateWidgetConfigTask();
        $result = $task->run(new FakeWidget(), ['label' => 'Hello', 'limit' => '7']);

        $this->assertSame(7, $result['limit']);
        $this->assertSame('Hello', $result['label']);
    }

    public function test_it_clamps_integer_to_schema_max(): void
    {
        $task = new ValidateWidgetConfigTask();
        $result = $task->run(new FakeWidget(), ['label' => 'Hello', 'limit' => 99]);

        $this->assertSame(10, $result['limit']);
    }

    public function test_it_requires_label(): void
    {
        $this->expectException(InvalidWidgetConfigException::class);

        (new ValidateWidgetConfigTask())->run(new FakeWidget(), []);
    }

    public function test_it_rejects_unsupported_size(): void
    {
        $this->expectException(InvalidWidgetConfigException::class);

        (new ValidateWidgetConfigTask())->run(new FakeWidget(), ['label' => 'x'], WidgetSizeEnum::FULL);
    }
}
