<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Widget\Tests\Unit;

use App\Containers\DashboardSection\Widget\Exceptions\WidgetNotFoundException;
use App\Containers\DashboardSection\Widget\Managers\WidgetRegistry;
use App\Containers\DashboardSection\Widget\Tests\Fakes\FakeWidget;
use Tests\TestCase;

class WidgetRegistryTest extends TestCase
{
    public function test_it_registers_and_returns_a_widget(): void
    {
        $registry = new WidgetRegistry();
        $widget = new FakeWidget();

        $registry->register($widget);

        $this->assertTrue($registry->has('test.fake'));
        $this->assertSame($widget, $registry->get('test.fake'));
        $this->assertSame(['test.fake'], $registry->types());
        $this->assertSame('Fake', $registry->catalog()[0]['name']);
    }

    public function test_it_throws_for_unknown_type(): void
    {
        $this->expectException(WidgetNotFoundException::class);

        (new WidgetRegistry())->get('missing.widget');
    }
}
