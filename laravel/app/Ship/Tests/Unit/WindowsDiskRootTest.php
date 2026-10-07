<?php declare(strict_types=1);

namespace App\Ship\Tests\Unit;

use App\Ship\Helpers\WindowsDiskRoot;
use Tests\TestCase;

class WindowsDiskRootTest extends TestCase
{
    public function test_it_uses_the_real_windows_drive_on_windows(): void
    {
        $this->assertSame('F:\\', WindowsDiskRoot::resolve(null, 'Windows', 'F'));
        $this->assertSame('D:\\', WindowsDiskRoot::resolve('', 'Windows', 'D:'));
    }

    public function test_it_uses_the_docker_mount_on_linux(): void
    {
        $this->assertSame(storage_path('mnt/f'), WindowsDiskRoot::resolve(null, 'Linux', 'F'));
    }

    public function test_it_prefers_an_explicit_root(): void
    {
        $this->assertSame('F:\\', WindowsDiskRoot::resolve('F:', 'Linux', 'F'));
        $this->assertSame('F:\\', WindowsDiskRoot::resolve('F:/', 'Windows', 'F'));
        $this->assertSame('/var/www/laravel/storage/mnt/f', WindowsDiskRoot::resolve(
            '/var/www/laravel/storage/mnt/f',
            'Windows',
            'F',
        ));
    }

    public function test_it_detects_paths_inside_the_root_on_either_os(): void
    {
        $this->assertTrue(WindowsDiskRoot::contains('F:\\', 'F:\\Music\\Artist\\track.mp3'));
        $this->assertTrue(WindowsDiskRoot::contains('/var/www/laravel/storage/mnt/f', '/var/www/laravel/storage/mnt/f/Music/Artist'));
        $this->assertFalse(WindowsDiskRoot::contains('F:\\Music', 'F:\\Images\\photo.jpg'));
        $this->assertFalse(WindowsDiskRoot::contains('/mnt/f/Music', '/mnt/f/Images/photo.jpg'));
    }
}
