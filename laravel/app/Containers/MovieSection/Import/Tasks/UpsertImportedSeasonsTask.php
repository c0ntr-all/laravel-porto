<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Import\Tasks;

use App\Containers\MovieSection\Episode\Data\DTO\EpisodeCreateData;
use App\Containers\MovieSection\Episode\Data\DTO\EpisodeUpdateData;
use App\Containers\MovieSection\Episode\Data\Repositories\EpisodeRepository;
use App\Containers\MovieSection\Import\Data\DTO\ParsedEpisodeDto;
use App\Containers\MovieSection\Import\Data\DTO\ParsedSeasonDto;
use App\Containers\MovieSection\Movie\Models\Movie;
use App\Containers\MovieSection\Season\Data\DTO\SeasonCreateData;
use App\Containers\MovieSection\Season\Data\DTO\SeasonUpdateData;
use App\Containers\MovieSection\Season\Data\Repositories\SeasonRepository;
use App\Containers\MovieSection\Season\Models\Season;
use App\Ship\Parents\Tasks\Task as ParentTask;

class UpsertImportedSeasonsTask extends ParentTask
{
    public function __construct(
        private readonly SeasonRepository $seasonRepository,
        private readonly EpisodeRepository $episodeRepository,
    ) {
    }

    /**
     * @param list<ParsedSeasonDto> $seasons
     * @return array{
     *     seasons_total: int,
     *     seasons_created: int,
     *     seasons_updated: int,
     *     episodes_total: int,
     *     episodes_created: int,
     *     episodes_updated: int
     * }
     */
    public function run(Movie $movie, array $seasons): array
    {
        $seasonsCreated = 0;
        $seasonsUpdated = 0;
        $episodesCreated = 0;
        $episodesUpdated = 0;
        $episodesTotal = 0;

        foreach ($seasons as $parsedSeason) {
            $existing = $this->seasonRepository->findByMovieIdAndNumber(
                (int) $movie->id,
                $parsedSeason->number,
                forUpdate: true,
            );

            if ($existing === null) {
                $season = $this->seasonRepository->create(SeasonCreateData::from([
                    'movie_id' => (int) $movie->id,
                    'kp_id' => $parsedSeason->kp_id,
                    'kp_season_id' => $parsedSeason->kp_season_id,
                    'kp_movie_id' => $parsedSeason->kp_movie_id,
                    'name' => $parsedSeason->name,
                    'en_name' => $parsedSeason->en_name,
                    'number' => $parsedSeason->number,
                    'air_date' => $parsedSeason->air_date,
                    'episodes_count' => $parsedSeason->episodes_count,
                    'duration' => $parsedSeason->duration,
                    'poster' => $parsedSeason->poster,
                    'poster_preview' => $parsedSeason->poster_preview,
                ]));
                $seasonsCreated++;
            } else {
                $season = $this->seasonRepository->update($existing, SeasonUpdateData::from(
                    $this->seasonUpdateAttributes($existing, $parsedSeason),
                ));
                $seasonsUpdated++;
            }

            foreach ($parsedSeason->episodes as $parsedEpisode) {
                $episodesTotal++;
                $episodeResult = $this->upsertEpisode($season, $parsedSeason, $parsedEpisode);
                if ($episodeResult) {
                    $episodesCreated++;
                } else {
                    $episodesUpdated++;
                }
            }
        }

        return [
            'seasons_total' => count($seasons),
            'seasons_created' => $seasonsCreated,
            'seasons_updated' => $seasonsUpdated,
            'episodes_total' => $episodesTotal,
            'episodes_created' => $episodesCreated,
            'episodes_updated' => $episodesUpdated,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function seasonUpdateAttributes(Season $existing, ParsedSeasonDto $parsed): array
    {
        $attributes = [
            'kp_movie_id' => $parsed->kp_movie_id,
            'name' => $parsed->name,
            'en_name' => $parsed->en_name,
            'air_date' => $parsed->air_date,
            'episodes_count' => $parsed->episodes_count,
            'duration' => $parsed->duration,
        ];

        if ($parsed->kp_id !== null) {
            $attributes['kp_id'] = $parsed->kp_id;
        }

        if ($parsed->kp_season_id !== null) {
            $attributes['kp_season_id'] = $parsed->kp_season_id;
        }

        if ($parsed->poster !== null && $parsed->poster !== '') {
            $attributes['poster'] = $parsed->poster;
        } elseif ($existing->poster === null || $existing->poster === '') {
            $attributes['poster'] = $parsed->poster;
        }

        if ($parsed->poster_preview !== null && $parsed->poster_preview !== '') {
            $attributes['poster_preview'] = $parsed->poster_preview;
        } elseif ($existing->poster_preview === null || $existing->poster_preview === '') {
            $attributes['poster_preview'] = $parsed->poster_preview;
        }

        return $attributes;
    }

    /**
     * @return bool true when created
     */
    private function upsertEpisode(
        Season $season,
        ParsedSeasonDto $parsedSeason,
        ParsedEpisodeDto $parsedEpisode,
    ): bool {
        $existing = $this->episodeRepository->findBySeasonIdAndNumber(
            (int) $season->id,
            $parsedEpisode->number,
            forUpdate: true,
        );

        $attributes = [
            'season_id' => (int) $season->id,
            'kp_id' => $parsedEpisode->kp_id,
            'kp_season_id' => $parsedSeason->kp_season_id,
            'name' => $parsedEpisode->name,
            'description' => $parsedEpisode->description,
            'en_description' => $parsedEpisode->en_description,
            'number' => $parsedEpisode->number,
            'duration' => $parsedEpisode->duration,
            'air_date' => $parsedEpisode->air_date,
            'still' => $parsedEpisode->still,
            'still_preview' => $parsedEpisode->still_preview,
        ];

        if ($existing === null) {
            $this->episodeRepository->create(EpisodeCreateData::from($attributes));

            return true;
        }

        $update = [
            'kp_season_id' => $parsedSeason->kp_season_id,
            'name' => $parsedEpisode->name,
            'description' => $parsedEpisode->description,
            'en_description' => $parsedEpisode->en_description,
            'duration' => $parsedEpisode->duration,
            'air_date' => $parsedEpisode->air_date,
        ];

        if ($parsedEpisode->kp_id !== null) {
            $update['kp_id'] = $parsedEpisode->kp_id;
        }

        if ($parsedEpisode->still !== null && $parsedEpisode->still !== '') {
            $update['still'] = $parsedEpisode->still;
        } elseif ($existing->still === null || $existing->still === '') {
            $update['still'] = $parsedEpisode->still;
        }

        if ($parsedEpisode->still_preview !== null && $parsedEpisode->still_preview !== '') {
            $update['still_preview'] = $parsedEpisode->still_preview;
        } elseif ($existing->still_preview === null || $existing->still_preview === '') {
            $update['still_preview'] = $parsedEpisode->still_preview;
        }

        $this->episodeRepository->update($existing, EpisodeUpdateData::from($update));

        return false;
    }
}
