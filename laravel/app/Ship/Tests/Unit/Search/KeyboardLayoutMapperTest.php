<?php declare(strict_types=1);

namespace App\Ship\Tests\Unit\Search;

use App\Ship\Support\Search\KeyboardLayoutMapper;
use PHPUnit\Framework\TestCase;

class KeyboardLayoutMapperTest extends TestCase
{
    private KeyboardLayoutMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mapper = new KeyboardLayoutMapper();
    }

    public function test_it_flips_english_layout_to_russian(): void
    {
        // "Тор" typed while English layout was active → "Njh"
        $this->assertSame('Тор', $this->mapper->flip('Njh'));
        $this->assertSame('привет', $this->mapper->flip('ghbdtn'));
    }

    public function test_it_flips_russian_layout_to_english(): void
    {
        $this->assertSame('Njh', $this->mapper->flip('Тор'));
        // "thor" typed while Russian layout was active → "ерщк"
        $this->assertSame('thor', $this->mapper->flip('ерщк'));
    }

    public function test_variants_include_original_and_flipped(): void
    {
        $this->assertSame(['Njh', 'Тор'], $this->mapper->variants('Njh'));
        $this->assertSame(['Тор', 'Njh'], $this->mapper->variants('Тор'));
    }

    public function test_variants_do_not_duplicate_when_flip_is_noop(): void
    {
        $this->assertSame(['123'], $this->mapper->variants('123'));
        $this->assertSame([], $this->mapper->variants('   '));
    }
}
