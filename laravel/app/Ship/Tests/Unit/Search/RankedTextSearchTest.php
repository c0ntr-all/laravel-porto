<?php declare(strict_types=1);

namespace App\Ship\Tests\Unit\Search;

use App\Containers\MovieSection\Movie\Models\Movie;
use App\Ship\Support\Search\RankedTextSearch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RankedTextSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_ranks_exact_match_before_glued_prefix(): void
    {
        $exact = Movie::factory()->create(['title' => 'Тор', 'created_at' => now()->subDay()]);
        $glued = Movie::factory()->create(['title' => 'Торнадо', 'created_at' => now()]);
        $token = Movie::factory()->create(['title' => 'Тор: Рагнарёк', 'created_at' => now()->subHours(2)]);
        Movie::factory()->create(['title' => 'Матрица']);

        $ids = Movie::query()
            ->tap(fn ($query) => (new RankedTextSearch())->apply($query, 'Тор', ['movies.title']))
            ->pluck('id')
            ->all();

        $this->assertSame(
            [(int) $exact->id, (int) $token->id, (int) $glued->id],
            array_map('intval', $ids),
        );
    }

    public function test_it_finds_title_typed_in_wrong_keyboard_layout(): void
    {
        $thor = Movie::factory()->create(['title' => 'Тор']);
        Movie::factory()->create(['title' => 'Матрица']);

        // "Тор" typed on English layout
        $ids = Movie::query()
            ->tap(fn ($query) => (new RankedTextSearch())->apply(
                $query,
                'Njh',
                ['movies.title'],
                ['movies.description'],
            ))
            ->pluck('id')
            ->all();

        $this->assertSame([(int) $thor->id], array_map('intval', $ids));
    }

    public function test_secondary_columns_match_without_outranking_title_hits(): void
    {
        $titleHit = Movie::factory()->create([
            'title' => 'Тор',
            'description' => 'Обычное описание',
        ]);
        $descriptionHit = Movie::factory()->create([
            'title' => 'Другой фильм',
            'description' => 'Фильм про Торнадо',
        ]);

        $ids = Movie::query()
            ->tap(fn ($query) => (new RankedTextSearch())->apply(
                $query,
                'Тор',
                ['movies.title'],
                ['movies.description'],
            ))
            ->pluck('id')
            ->all();

        $this->assertSame((int) $titleHit->id, (int) $ids[0]);
        $this->assertContains((int) $descriptionHit->id, array_map('intval', $ids));
    }
}
