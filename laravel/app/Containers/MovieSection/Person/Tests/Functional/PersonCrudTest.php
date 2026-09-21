<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Person\Tests\Functional;

use App\Containers\AppSection\User\Models\User;
use App\Containers\MovieSection\Movie\Models\Movie;
use App\Containers\MovieSection\Person\Models\Person;
use App\Containers\MovieSection\Profession\Models\Profession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_unauthenticated_user_cannot_list_persons(): void
    {
        $this->getJson('/api/v1/movie/persons')->assertUnauthorized();
    }

    public function test_user_can_create_person(): void
    {
        $actor = Profession::factory()->create(['en_name' => 'actor', 'name' => 'актеры']);

        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/movie/persons', [
                'kp_id' => 7987,
                'profession_id' => $actor->id,
                'name' => 'Аль Пачино',
                'en_name' => 'Al Pacino',
                'photo' => 'https://example.com/pacino.jpg',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.type', 'movie_persons')
            ->assertJsonPath('data.attributes.kp_id', 7987)
            ->assertJsonPath('data.attributes.name', 'Аль Пачино')
            ->assertJsonPath('data.attributes.en_name', 'Al Pacino')
            ->assertJsonPath('data.attributes.profession_id', $actor->id);

        $this->assertDatabaseHas('movie_persons', [
            'kp_id' => 7987,
            'name' => 'Аль Пачино',
            'profession_id' => $actor->id,
        ]);
        $this->assertDatabaseHas('movie_person_profession', [
            'profession_id' => $actor->id,
        ]);
    }

    public function test_user_can_list_filter_by_name_and_profession_and_include_relations(): void
    {
        $actor = Profession::factory()->create(['en_name' => 'actor', 'name' => 'актеры']);
        $director = Profession::factory()->create(['en_name' => 'director', 'name' => 'режиссеры']);
        $movie = Movie::factory()->create(['title' => 'The Godfather']);

        $pacino = Person::factory()->create([
            'name' => 'Аль Пачино',
            'en_name' => 'Al Pacino',
            'profession_id' => $actor->id,
        ]);
        $coppola = Person::factory()->create([
            'name' => 'Фрэнсис Форд Коппола',
            'en_name' => 'Francis Ford Coppola',
            'profession_id' => $director->id,
        ]);
        $pacino->professions()->attach($actor);
        $coppola->professions()->attach($director);
        $movie->persons()->attach($pacino->id, [
            'profession_id' => $actor->id,
            'description' => 'Michael Corleone',
        ]);

        $byName = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/movie/persons?filter[name]=Пачино');
        $byName->assertOk();
        $this->assertCount(1, $byName->json('data'));
        $this->assertSame($pacino->id, (int) $byName->json('data.0.id'));

        $byProfession = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/movie/persons?filter[profession]=director');
        $byProfession->assertOk();
        $this->assertCount(1, $byProfession->json('data'));
        $this->assertSame($coppola->id, (int) $byProfession->json('data.0.id'));

        $included = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/movie/persons?filter[name]=Pacino&include=profession,movies');
        $included->assertOk();
        $types = collect($included->json('included'))->pluck('type')->unique()->values()->all();
        $this->assertContains('movie_professions', $types);
        $this->assertContains('movies', $types);
    }

    public function test_user_can_get_update_and_delete_person(): void
    {
        $actor = Profession::factory()->create(['en_name' => 'actor']);
        $person = Person::factory()->create([
            'name' => 'Old Name',
            'profession_id' => $actor->id,
        ]);

        $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/movie/persons/'.$person->id)
            ->assertOk()
            ->assertJsonPath('data.attributes.name', 'Old Name');

        $this->actingAs($this->user, 'api')
            ->patchJson('/api/v1/movie/persons/'.$person->id, [
                'name' => 'New Name',
            ])
            ->assertOk()
            ->assertJsonPath('data.attributes.name', 'New Name');

        $this->actingAs($this->user, 'api')
            ->deleteJson('/api/v1/movie/persons/'.$person->id)
            ->assertOk();

        $this->assertDatabaseMissing('movie_persons', ['id' => $person->id]);
    }
}
