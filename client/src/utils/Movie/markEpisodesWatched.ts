import { movieApi } from 'src/api/requests/movieApi'
import { IMovieEpisode } from 'src/types/Movie'

export async function markEpisodesWatchedIfNeeded (
  episodes: IMovieEpisode[],
  episodeIds: string[]
): Promise<void> {
  const idSet = new Set(episodeIds)
  const toMark = episodes.filter(
    episode => idSet.has(episode.id) && !episode.is_watched
  )

  if (!toMark.length) {
    return
  }

  await Promise.all(
    toMark.map(episode => movieApi.markEpisodeWatched(episode.id))
  )
}
