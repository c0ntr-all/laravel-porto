import { IPlaylist } from 'src/types'

export function playlistTracksCount (playlist: Pick<IPlaylist, 'tracks_count' | 'tracks'>): number {
  if (typeof playlist.tracks_count === 'number' && Number.isFinite(playlist.tracks_count)) {
    return Math.max(0, playlist.tracks_count)
  }

  return playlist.tracks?.length ?? 0
}

export function formatPlaylistTracksCount (playlist: Pick<IPlaylist, 'tracks_count' | 'tracks'>): string {
  const count = playlistTracksCount(playlist)

  return `${count} ${count === 1 ? 'track' : 'tracks'}`
}
