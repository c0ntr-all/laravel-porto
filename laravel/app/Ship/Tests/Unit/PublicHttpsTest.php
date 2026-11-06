<?php declare(strict_types=1);

namespace App\Ship\Tests\Unit;

use App\Ship\Helpers\PublicHttps;
use InvalidArgumentException;
use Tests\TestCase;

class PublicHttpsTest extends TestCase
{
    public function test_rejects_non_https_and_private_hosts(): void
    {
        $blocked = [
            'http://example.com/a.jpg',
            'https://127.0.0.1/a.jpg',
            'https://10.1.1.1/a.jpg',
            'https://192.168.0.5/a.jpg',
            'https://172.16.0.1/a.jpg',
            'https://169.254.1.1/a.jpg',
            'https://localhost/a.jpg',
            'https://user:pass@example.com/a.jpg',
        ];

        foreach ($blocked as $url) {
            try {
                PublicHttps::assertPublic($url);
                $this->fail($url . ' was accepted');
            } catch (InvalidArgumentException) {
                $this->assertTrue(true);
            }
        }
    }
}
