<?php

declare(strict_types=1);

namespace App\Containers\NotesSection\Note\Tests\Unit;

use App\Containers\NotesSection\Note\Support\NoteLinkParser;
use App\Containers\NotesSection\Note\Support\NotePlainText;
use App\Containers\NotesSection\Note\Support\PinOrder;
use PHPUnit\Framework\TestCase;

class NoteSupportTest extends TestCase
{
    public function test_plain_text_drops_tags_and_keeps_paragraph_breaks_as_spaces(): void
    {
        $this->assertSame(
            'Первый второй',
            NotePlainText::fromHtml('<p>Первый</p><p>второй</p>'),
        );
        $this->assertSame('', NotePlainText::fromHtml(null));
        $this->assertSame('A & B', NotePlainText::fromHtml('<p>A &amp; B</p>'));
    }

    public function test_parser_reads_unique_wiki_labels(): void
    {
        $this->assertSame(
            ['Рецепт', 'Другое'],
            NoteLinkParser::labels("Смотри [[Рецепт]] и [[ Рецепт ]] плюс [[Другое]]\n[[не\nсработает]]"),
        );
        $this->assertSame([], NoteLinkParser::labels('без ссылок'));
    }

    public function test_pin_order_fits_between_neighbors_until_the_gap_is_gone(): void
    {
        $this->assertSame(1024, PinOrder::between(null, null));
        $this->assertSame(512, PinOrder::between(null, 1024));
        $this->assertSame(2048, PinOrder::between(1024, null));
        $this->assertSame(1536, PinOrder::between(1024, 2048));
        $this->assertNull(PinOrder::between(1, 2));
        $this->assertNull(PinOrder::between(null, 1));
    }
}
