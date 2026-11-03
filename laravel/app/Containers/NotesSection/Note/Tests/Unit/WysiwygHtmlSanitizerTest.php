<?php

declare(strict_types=1);

namespace App\Containers\NotesSection\Note\Tests\Unit;

use App\Containers\NotesSection\Note\Support\WysiwygHtmlSanitizer;
use PHPUnit\Framework\TestCase;

class WysiwygHtmlSanitizerTest extends TestCase
{
    private WysiwygHtmlSanitizer $sanitizer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sanitizer = new WysiwygHtmlSanitizer;
    }

    public function test_keeps_editor_formatting_and_cyrillic_text(): void
    {
        $clean = $this->sanitizer->sanitize(
            '<p style="text-align: center">Привет <b>мир</b> <a href="https://example.com" target="_blank">ссылка</a></p>'
        );

        $this->assertNotNull($clean);
        $this->assertStringContainsString('Привет', $clean);
        $this->assertStringContainsString('<b>мир</b>', $clean);
        $this->assertStringContainsString('href="https://example.com"', $clean);
        $this->assertStringContainsString('text-align: center', $clean);
        $this->assertStringContainsString('rel="noopener noreferrer"', $clean);
    }

    public function test_drops_scripts_event_handlers_and_unsafe_urls(): void
    {
        $clean = $this->sanitizer->sanitize(
            '<p onclick="alert(1)">Hi</p><script>alert(1)</script><a href="javascript:alert(1)">x</a><img src="data:text/html,hi" onerror="alert(1)">'
        );

        $this->assertNotNull($clean);
        $lower = strtolower($clean);
        $this->assertStringContainsString('Hi', $clean);
        $this->assertStringContainsString('x', $clean);
        $this->assertStringNotContainsString('script', $lower);
        $this->assertStringNotContainsString('onclick', $lower);
        $this->assertStringNotContainsString('onerror', $lower);
        $this->assertStringNotContainsString('javascript:', $lower);
        $this->assertStringNotContainsString('data:', $lower);
    }

    public function test_null_and_blank_content_stay_empty(): void
    {
        $this->assertNull($this->sanitizer->sanitize(null));
        $this->assertSame('', $this->sanitizer->sanitize('   '));
    }
}
