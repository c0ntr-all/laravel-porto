<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Album\Tests\Unit;

use App\Containers\MusicSection\Album\Data\Filters\AlbumNameFilter;
use App\Containers\MusicSection\Album\Models\Album;
use App\Ship\Parents\QueryBuilder\QueryBuilder;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Tests\TestCase;

class AlbumNameFilterTest extends TestCase
{
    public function test_it_matches_name_and_edition_case_insensitively(): void
    {
        $request = Request::create('/api/v1/music/albums', 'GET', [
            'filter' => ['name' => 'Digipack'],
        ]);
        $this->app->instance('request', $request);

        $sql = QueryBuilder::for(Album::class, $request)
            ->allowedFilters([AllowedFilter::custom('name', new AlbumNameFilter())])
            ->toSql();

        $this->assertStringContainsString('LOWER(', $sql);
        $this->assertStringContainsString('edition', $sql);
        $this->assertStringContainsString('like', strtolower($sql));
    }

    public function test_empty_name_does_not_add_a_constraint(): void
    {
        $request = Request::create('/api/v1/music/albums', 'GET', [
            'filter' => ['name' => '   '],
        ]);
        $this->app->instance('request', $request);

        $sql = QueryBuilder::for(Album::class, $request)
            ->allowedFilters([AllowedFilter::custom('name', new AlbumNameFilter())])
            ->toSql();

        $this->assertStringNotContainsString('like', strtolower($sql));
    }
}
