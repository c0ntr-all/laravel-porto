<?php declare(strict_types=1);

namespace App\Ship\Tests\Unit;

use App\Ship\Helpers\WindowsPathHelper;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Tests\TestCase;

class WindowsPathHelperTest extends TestCase
{
    public function test_it_normalizes_mixed_slashes(): void
    {
        $this->assertSame(
            'F:\\Images\\vacation\\photo.jpg',
            WindowsPathHelper::normalizeWindows('F:/Images/vacation/photo.jpg'),
        );
    }

    public function test_it_maps_windows_path_to_linux_disk(): void
    {
        $root = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'gallery-win-' . uniqid('', true);
        mkdir($root, 0777, true);

        config(['filesystems.disks.windows_f.root' => $root]);
        Storage::forgetDisk('windows_f');

        $linux = WindowsPathHelper::toLinux('F:\\Images\\photo.jpg', 'windows_f');

        $this->assertSame(
            rtrim(str_replace('\\', '/', $root), '/') . '/Images/photo.jpg',
            str_replace('\\', '/', $linux),
        );

        rmdir($root);
    }

    public function test_it_rejects_path_outside_root(): void
    {
        $this->assertFalse(
            WindowsPathHelper::isUnderRoot('F:\\Other\\photo.jpg', 'F:\\Images\\'),
        );
        $this->assertTrue(
            WindowsPathHelper::isUnderRoot('F:\\Images\\folder\\photo.jpg', 'F:\\Images\\'),
        );
    }

    public function test_it_rejects_non_windows_path(): void
    {
        $this->expectException(InvalidArgumentException::class);
        WindowsPathHelper::normalizeWindows('/tmp/photo.jpg');
    }
}
