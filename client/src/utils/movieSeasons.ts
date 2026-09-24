import { IMovieEpisode, IMovieSeason } from 'src/types/Movie'

export function seasonWatchedCount(season: IMovieSeason): number {
  return season.episodes.filter(episode => episode.is_watched).length
}

export function seasonEpisodesTotal(season: IMovieSeason): number {
  return Math.max(season.episodes.length, season.episodes_count ?? 0)
}

export function withSeasonWatched(season: IMovieSeason, watched: boolean): IMovieSeason {
  return {
    ...season,
    is_watched: watched,
    episodes: season.episodes.map(episode => ({
      ...episode,
      is_watched: watched
    }))
  }
}

export function withEpisodeWatched(
  season: IMovieSeason,
  episodeId: string,
  watched: boolean
): IMovieSeason {
  return {
    ...season,
    episodes: season.episodes.map(episode => (
      episode.id === episodeId
        ? { ...episode, is_watched: watched }
        : episode
    ))
  }
}

export function replaceSeason(seasons: IMovieSeason[], next: IMovieSeason): IMovieSeason[] {
  return seasons.map(season => (season.id === next.id ? next : season))
}

export function replaceEpisode(
  seasons: IMovieSeason[],
  episode: IMovieEpisode
): IMovieSeason[] {
  return seasons.map(season => {
    if (
      String(season.id) !== String(episode.season_id) &&
      !season.episodes.some(item => item.id === episode.id)
    ) {
      return season
    }

    return {
      ...season,
      episodes: season.episodes.map(item => (item.id === episode.id
        ? { ...item, ...episode }
        : item
      ))
    }
  })
}
