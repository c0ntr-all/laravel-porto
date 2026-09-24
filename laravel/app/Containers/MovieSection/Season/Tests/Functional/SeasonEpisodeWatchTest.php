<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Season\Tests\Functional;

use App\Containers\AppSection\User\Models\User;
use App\Containers\MovieSection\Episode\Models\Episode;
use App\Containers\MovieSection\Folder\Enums\SystemMovieFolderEnum;
use App\Containers\MovieSection\Folder\Models\Folder;
use App\Containers\MovieSection\Movie\Enums\MovieTypeEnum;
use App\Containers\MovieSection\Movie\Models\Movie;
use App\Containers\MovieSection\Season\Jobs\MarkSeasonEpisodesWatchedJob;
use App\Containers\MovieSection\Season\Jobs\UnmarkSeasonEpisodesWatchedJob;
use App\Containers\MovieSection\Season\Models\Season;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SeasonEpisodeWatchTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_user_can_mark_and_unmark_episode_watched(): void
    {
        $movie = Movie::factory()->create(['type' => MovieTypeEnum::TV_SERIES]);
        $season = Season::factory()->create(['movie_id' => $movie->id, 'number' => 1]);
        $episode = Episode::factory()->create(['season_id' => $season->id, 'number' => 1]);

        $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/movie/episodes/'.$episode->id.'/watch')
            ->assertOk()
            ->assertJsonPath('data.attributes.is_watched', true);

        $this->assertDatabaseHas('movie_episode_watches', [
            'user_id' => $this->user->id,
            'episode_id' => $episode->id,
        ]);

        $watchedFolder = Folder::query()
            ->where('user_id', $this->user->id)
            ->where('slug', SystemMovieFolderEnum::WATCHED->value)
            ->firstOrFail();
        $this->assertDatabaseHas('movie_folder_movie', [
            'folder_id' => $watchedFolder->id,
            'movie_id' => $movie->id,
        ]);

        $this->actingAs($this->user, 'api')
            ->deleteJson('/api/v1/movie/episodes/'.$episode->id.'/watch')
            ->assertOk()
            ->assertJsonPath('data.attributes.is_watched', false);

        $this->assertDatabaseMissing('movie_episode_watches', [
            'user_id' => $this->user->id,
            'episode_id' => $episode->id,
        ]);
    }

    public function test_marking_season_watched_dispatches_job_for_episodes(): void
    {
        Queue::fake();

        $movie = Movie::factory()->create(['type' => MovieTypeEnum::TV_SERIES]);
        $season = Season::factory()->create(['movie_id' => $movie->id, 'number' => 1]);
        Episode::factory()->create(['season_id' => $season->id, 'number' => 1]);
        Episode::factory()->create(['season_id' => $season->id, 'number' => 2]);

        $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/movie/seasons/'.$season->id.'/watch')
            ->assertOk()
            ->assertJsonPath('data.attributes.is_watched', true);

        $this->assertDatabaseHas('movie_season_watches', [
            'user_id' => $this->user->id,
            'season_id' => $season->id,
        ]);

        Queue::assertPushed(MarkSeasonEpisodesWatchedJob::class, function (MarkSeasonEpisodesWatchedJob $job) use ($season): bool {
            return $job->seasonId === $season->id && $job->userId === $this->user->id;
        });
    }

    public function test_season_watch_job_marks_all_child_episodes(): void
    {
        $movie = Movie::factory()->create(['type' => MovieTypeEnum::TV_SERIES]);
        $season = Season::factory()->create(['movie_id' => $movie->id, 'number' => 1]);
        $e1 = Episode::factory()->create(['season_id' => $season->id, 'number' => 1]);
        $e2 = Episode::factory()->create(['season_id' => $season->id, 'number' => 2]);

        (new MarkSeasonEpisodesWatchedJob($season->id, $this->user->id))->handle(
            app(\App\Containers\MovieSection\Season\Tasks\MarkSeasonEpisodesWatchedTask::class),
        );

        $this->assertDatabaseHas('movie_episode_watches', [
            'user_id' => $this->user->id,
            'episode_id' => $e1->id,
        ]);
        $this->assertDatabaseHas('movie_episode_watches', [
            'user_id' => $this->user->id,
            'episode_id' => $e2->id,
        ]);
    }

    public function test_unmarking_season_dispatches_job_to_clear_episode_watches(): void
    {
        Queue::fake();

        $movie = Movie::factory()->create(['type' => MovieTypeEnum::TV_SERIES]);
        $season = Season::factory()->create(['movie_id' => $movie->id, 'number' => 1]);

        $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/movie/seasons/'.$season->id.'/watch')
            ->assertOk();

        Queue::fake();

        $this->actingAs($this->user, 'api')
            ->deleteJson('/api/v1/movie/seasons/'.$season->id.'/watch')
            ->assertOk()
            ->assertJsonPath('data.attributes.is_watched', false);

        $this->assertDatabaseMissing('movie_season_watches', [
            'user_id' => $this->user->id,
            'season_id' => $season->id,
        ]);

        Queue::assertPushed(UnmarkSeasonEpisodesWatchedJob::class, function (UnmarkSeasonEpisodesWatchedJob $job) use ($season): bool {
            return $job->seasonId === $season->id && $job->userId === $this->user->id;
        });
    }
}
