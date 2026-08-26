import { computed, ref } from 'vue'
import { defineStore } from 'pinia'
import { api } from 'src/boot/axios'
import { handleApiError } from 'src/utils/jsonapi'
import { audioEngine } from 'src/services/audio-engine'
import { formatPlaybackTime, parseDuration } from 'src/utils/playbackTime'
import { ITrack } from 'src/components/client/Music/types'

export type RepeatMode = 'off' | 'all' | 'one'
export type PlaybackStatus = 'idle' | 'loading' | 'playing' | 'paused' | 'stopped'

interface PlayerPreferences {
  volume: number
  muted: boolean
  shuffle: boolean
  repeat: RepeatMode
}

const PREFERENCES_KEY = 'home-portal.music-player'
const DEFAULT_VOLUME = 0.7
const SEEK_STEP_SECONDS = 10
const PREVIOUS_RESTART_SECONDS = 3
const SCROBBLE_MAX_SECONDS = 240
const REPEAT_ORDER: RepeatMode[] = ['off', 'all', 'one']

function getTrackAudioSrc(track: ITrack): string {
  const withLink = track as ITrack & { link?: string }
  if (withLink.link) {
    return withLink.link
  }

  const url = `${process.env.host}/v1/music/tracks/${track.id}/play`
  const token = typeof localStorage === 'undefined' ? null : localStorage.getItem('access_token')

  if (!token) {
    return url
  }

  return `${url}?access_token=${encodeURIComponent(token)}`
}

function cloneTracks(tracks: ITrack[]): ITrack[] {
  return tracks.map(track => ({ ...track }))
}

function isSameTrackList(left: ITrack[], right: ITrack[]): boolean {
  if (left.length !== right.length) {
    return false
  }

  return left.every((track, index) => track.id === right[index].id)
}

function shuffleTracks(tracks: ITrack[], currentId?: string | null): ITrack[] {
  const pool = currentId ? tracks.filter(track => track.id !== currentId) : [...tracks]

  for (let index = pool.length - 1; index > 0; index--) {
    const randomIndex = Math.floor(Math.random() * (index + 1))
    const current = pool[index]
    pool[index] = pool[randomIndex]
    pool[randomIndex] = current
  }

  if (!currentId) {
    return pool
  }

  const currentTrack = tracks.find(track => track.id === currentId)
  return currentTrack ? [currentTrack, ...pool] : pool
}

function readPreferences(): PlayerPreferences {
  const defaults: PlayerPreferences = {
    volume: DEFAULT_VOLUME,
    muted: false,
    shuffle: false,
    repeat: 'off'
  }

  if (typeof window === 'undefined') {
    return defaults
  }

  try {
    const raw = localStorage.getItem(PREFERENCES_KEY)
    if (!raw) {
      return defaults
    }

    const parsed = JSON.parse(raw) as Partial<PlayerPreferences>
    const volume = typeof parsed.volume === 'number' ? parsed.volume : defaults.volume

    return {
      volume: Math.min(Math.max(volume, 0), 1),
      muted: Boolean(parsed.muted),
      shuffle: Boolean(parsed.shuffle),
      repeat: REPEAT_ORDER.includes(parsed.repeat as RepeatMode)
        ? parsed.repeat as RepeatMode
        : defaults.repeat
    }
  } catch {
    return defaults
  }
}

function writePreferences(preferences: PlayerPreferences): void {
  if (typeof window === 'undefined') {
    return
  }

  localStorage.setItem(PREFERENCES_KEY, JSON.stringify(preferences))
}

function getScrobbleThreshold(duration: number): number {
  if (!Number.isFinite(duration) || duration <= 0) {
    return 30
  }

  return Math.min(Math.max(duration / 2, 1), SCROBBLE_MAX_SECONDS)
}

export const useMusicPlayer = defineStore('musicPlayer', () => {
  const preferences = readPreferences()

  const currentTrack = ref<ITrack | null>(null)
  const originalPlaylist = ref<ITrack[]>([])
  const queue = ref<ITrack[]>([])
  const currentIndex = ref(-1)
  const status = ref<PlaybackStatus>('idle')
  const currentTime = ref(0)
  const duration = ref(0)
  const volume = ref(preferences.volume)
  const muted = ref(preferences.muted)
  const shuffleEnabled = ref(preferences.shuffle)
  const repeatMode = ref<RepeatMode>(preferences.repeat)
  const isScrobbled = ref(false)

  let initialized = false
  let scrobbleInFlight = false
  let listenedSeconds = 0
  let lastTimeUpdate = 0

  const playlist = computed(() => queue.value)
  const hasTrack = computed(() => currentTrack.value !== null)
  const isPlaying = computed(() => status.value === 'playing')
  const isPaused = computed(() => status.value === 'paused' || status.value === 'stopped')
  const progress = computed(() => (
    duration.value > 0 ? (currentTime.value / duration.value) * 100 : 0
  ))
  const timePassed = computed(() => formatPlaybackTime(currentTime.value))
  const timeTotal = computed(() => formatPlaybackTime(duration.value))
  const trackTitle = computed(() => {
    if (!currentTrack.value) {
      return 'No track selected'
    }

    const artist = currentTrack.value.artist || 'Unknown artist'
    return `${artist} - ${currentTrack.value.name}`
  })
  const hasPrevious = computed(() => (
    queue.value.length > 0 && (currentIndex.value > 0 || repeatMode.value === 'all')
  ))
  const hasNext = computed(() => (
    queue.value.length > 0 && (
      currentIndex.value < queue.value.length - 1 ||
      repeatMode.value === 'all'
    )
  ))
  const volumePercent = computed(() => Math.round(volume.value * 100))

  function isIgnorablePlaybackError(error: unknown): boolean {
    return error instanceof DOMException && (
      error.name === 'AbortError' ||
      error.name === 'NotAllowedError'
    )
  }

  function notifyPlaybackError(error: unknown): void {
    if (isIgnorablePlaybackError(error)) {
      return
    }

    handleApiError(error)
  }

  function persistPreferences(): void {
    writePreferences({
      volume: volume.value,
      muted: muted.value,
      shuffle: shuffleEnabled.value,
      repeat: repeatMode.value
    })
  }

  function syncIndex(trackId?: string | null): void {
    const id = trackId ?? currentTrack.value?.id
    if (!id) {
      currentIndex.value = queue.value.length ? 0 : -1
      return
    }

    const index = queue.value.findIndex(track => track.id === id)
    currentIndex.value = index
  }

  function resetProgress(track?: ITrack | null): void {
    currentTime.value = 0
    duration.value = track ? parseDuration(track.duration) : 0
  }

  function updateMediaSession(): void {
    if (typeof navigator === 'undefined' || !('mediaSession' in navigator)) {
      return
    }

    if (!currentTrack.value) {
      navigator.mediaSession.metadata = null
      navigator.mediaSession.playbackState = 'none'
      return
    }

    navigator.mediaSession.metadata = new MediaMetadata({
      title: currentTrack.value.name,
      artist: currentTrack.value.artist || 'Unknown artist',
      artwork: currentTrack.value.image
        ? [{ src: currentTrack.value.image, sizes: '512x512', type: 'image/jpeg' }]
        : []
    })
    navigator.mediaSession.playbackState = isPlaying.value ? 'playing' : 'paused'
  }

  function bindMediaSessionHandlers(): void {
    if (typeof navigator === 'undefined' || !('mediaSession' in navigator)) {
      return
    }

    const bind = (
      action: 'play' | 'pause' | 'stop' | 'previoustrack' | 'nexttrack' | 'seekbackward' | 'seekforward' | 'seekto',
      handler: (details: { seekOffset?: number; seekTime?: number }) => void
    ) => {
      try {
        navigator.mediaSession.setActionHandler(action, handler)
      } catch {
        // Not every browser supports every media session action.
      }
    }

    bind('play', () => { play() })
    bind('pause', () => { pause() })
    bind('stop', () => { stop() })
    bind('previoustrack', () => { previous() })
    bind('nexttrack', () => { next() })
    bind('seekbackward', details => { seekBy(-(details.seekOffset ?? SEEK_STEP_SECONDS)) })
    bind('seekforward', details => { seekBy(details.seekOffset ?? SEEK_STEP_SECONDS) })
    bind('seekto', details => {
      if (typeof details.seekTime === 'number') {
        seek(details.seekTime)
      }
    })
  }

  async function scrobbleCurrentTrack(): Promise<void> {
    const track = currentTrack.value
    if (!track || isScrobbled.value || scrobbleInFlight) {
      return
    }

    scrobbleInFlight = true

    try {
      await api.post('v1/music/history', {
        track_id: Number(track.id)
      })
      isScrobbled.value = true
    } catch (error) {
      handleApiError(error)
    } finally {
      scrobbleInFlight = false
    }
  }

  function resetScrobbleState(): void {
    isScrobbled.value = false
    scrobbleInFlight = false
    listenedSeconds = 0
    lastTimeUpdate = 0
  }

  function maybeScrobble(): void {
    if (isScrobbled.value || duration.value <= 0) {
      return
    }

    if (listenedSeconds >= getScrobbleThreshold(duration.value)) {
      void scrobbleCurrentTrack()
    }
  }

  function applyQueue(tracks: ITrack[], currentId?: string | null): void {
    originalPlaylist.value = cloneTracks(tracks)
    queue.value = shuffleEnabled.value
      ? shuffleTracks(originalPlaylist.value, currentId)
      : cloneTracks(tracks)
    syncIndex(currentId)
  }

  async function loadAndPlay(track: ITrack): Promise<void> {
    init()
    currentTrack.value = track
    resetScrobbleState()
    resetProgress(track)
    status.value = 'loading'
    syncIndex(track.id)
    updateMediaSession()
    audioEngine.load(getTrackAudioSrc(track))

    try {
      await audioEngine.play()
      status.value = 'playing'
    } catch (error) {
      status.value = 'paused'
      notifyPlaybackError(error)
    }

    updateMediaSession()
  }

  async function playAt(index: number): Promise<void> {
    const track = queue.value[index]
    if (!track) {
      stop()
      return
    }

    currentIndex.value = index
    await loadAndPlay(track)
  }

  function init(): void {
    if (initialized || typeof window === 'undefined') {
      return
    }

    initialized = true

    audioEngine.on('timeupdate', payload => {
      const nextTime = Number.isFinite(payload.currentTime) ? payload.currentTime : 0

      if (status.value === 'playing' && lastTimeUpdate > 0) {
        const delta = nextTime - lastTimeUpdate
        if (delta > 0 && delta < 1.5) {
          listenedSeconds += delta
        }
      }

      lastTimeUpdate = nextTime
      currentTime.value = nextTime
      if (payload.duration > 0) {
        duration.value = payload.duration
      }
      maybeScrobble()
    })
    audioEngine.on('durationchange', payload => {
      if (payload.duration > 0) {
        duration.value = payload.duration
      }
    })
    audioEngine.on('waiting', () => {
      if (status.value === 'playing') {
        status.value = 'loading'
      }
    })
    audioEngine.on('playing', () => {
      status.value = 'playing'
      updateMediaSession()
    })
    audioEngine.on('play', () => {
      status.value = 'playing'
      updateMediaSession()
    })
    audioEngine.on('pause', () => {
      if (status.value !== 'stopped') {
        status.value = 'paused'
      }
      updateMediaSession()
    })
    audioEngine.on('ended', () => {
      void handleEnded()
    })
    audioEngine.on('error', payload => {
      status.value = 'paused'
      handleApiError(new Error(payload.message))
    })
    audioEngine.on('volumechange', payload => {
      volume.value = payload.volume
      muted.value = payload.muted
      persistPreferences()
    })

    audioEngine.setVolume(volume.value)
    audioEngine.setMuted(muted.value)
    bindMediaSessionHandlers()
  }

  async function play(): Promise<void> {
    init()

    if (!currentTrack.value) {
      const firstTrack = queue.value[0]
      if (!firstTrack) {
        return
      }
      await loadAndPlay(firstTrack)
      return
    }

    if (!audioEngine.src) {
      await loadAndPlay(currentTrack.value)
      return
    }

    try {
      await audioEngine.play()
      status.value = 'playing'
    } catch (error) {
      status.value = 'paused'
      notifyPlaybackError(error)
    }

    updateMediaSession()
  }

  function pause(): void {
    if (status.value === 'stopped') {
      return
    }

    audioEngine.pause()
    status.value = 'paused'
    updateMediaSession()
  }

  async function toggle(): Promise<void> {
    if (isPlaying.value) {
      pause()
      return
    }

    await play()
  }

  function stop(): void {
    status.value = currentTrack.value ? 'stopped' : 'idle'
    audioEngine.stop()
    currentTime.value = 0
    updateMediaSession()
  }

  function seek(time: number): void {
    audioEngine.seek(time)
    currentTime.value = audioEngine.currentTime
  }

  function seekBy(delta: number): void {
    seek(currentTime.value + delta)
  }

  function seekBackward(): void {
    seekBy(-SEEK_STEP_SECONDS)
  }

  function seekForward(): void {
    seekBy(SEEK_STEP_SECONDS)
  }

  function seekToProgress(percent: number): void {
    if (duration.value <= 0) {
      return
    }

    seek((percent / 100) * duration.value)
  }

  function setVolume(nextVolume: number): void {
    init()
    volume.value = Math.min(Math.max(nextVolume, 0), 1)
    audioEngine.setVolume(volume.value)
    persistPreferences()
  }

  function setVolumePercent(percent: number): void {
    setVolume(percent / 100)
  }

  function toggleMute(): void {
    init()
    muted.value = !muted.value
    audioEngine.setMuted(muted.value)
    persistPreferences()
  }

  function setPlaylist(tracks: ITrack[], currentId?: string | null): void {
    applyQueue(tracks, currentId ?? currentTrack.value?.id)
  }

  function addToPlaylist(tracks: ITrack[]): void {
    const existingIds = new Set(originalPlaylist.value.map(track => track.id))
    const uniqueTracks = tracks.filter(track => !existingIds.has(track.id))

    if (!uniqueTracks.length) {
      return
    }

    applyQueue([...originalPlaylist.value, ...uniqueTracks], currentTrack.value?.id)
  }

  function clearPlaylist(): void {
    originalPlaylist.value = []
    queue.value = []
    currentIndex.value = -1
  }

  async function playTrack(track: ITrack, sourcePlaylist?: ITrack[]): Promise<void> {
    if (sourcePlaylist) {
      setPlaylist(sourcePlaylist, track.id)
    } else if (!queue.value.some(item => item.id === track.id)) {
      applyQueue([track], track.id)
    } else {
      syncIndex(track.id)
    }

    await loadAndPlay(track)
  }

  async function toggleTrack(track: ITrack, sourcePlaylist?: ITrack[]): Promise<void> {
    const isCurrent = currentTrack.value?.id === track.id

    if (sourcePlaylist && !isSameTrackList(originalPlaylist.value, sourcePlaylist)) {
      setPlaylist(sourcePlaylist, track.id)
    } else if (sourcePlaylist && !originalPlaylist.value.some(item => item.id === track.id)) {
      setPlaylist(sourcePlaylist, track.id)
    }

    if (isCurrent) {
      await toggle()
      return
    }

    await playTrack(track)
  }

  async function previous(): Promise<void> {
    if (!queue.value.length) {
      seek(0)
      return
    }

    if (currentTime.value > PREVIOUS_RESTART_SECONDS) {
      seek(0)
      if (status.value === 'stopped') {
        await play()
      }
      return
    }

    if (currentIndex.value > 0) {
      await playAt(currentIndex.value - 1)
      return
    }

    if (repeatMode.value === 'all') {
      await playAt(queue.value.length - 1)
      return
    }

    seek(0)
  }

  async function next(): Promise<void> {
    if (!queue.value.length) {
      stop()
      return
    }

    if (currentIndex.value < queue.value.length - 1) {
      await playAt(currentIndex.value + 1)
      return
    }

    if (repeatMode.value === 'all') {
      await playAt(0)
      return
    }

    stop()
  }

  async function handleEnded(): Promise<void> {
    if (repeatMode.value === 'one') {
      seek(0)
      await play()
      return
    }

    if (currentIndex.value < queue.value.length - 1) {
      await next()
      return
    }

    if (repeatMode.value === 'all' && queue.value.length) {
      await playAt(0)
      return
    }

    stop()
  }

  function toggleShuffle(): void {
    shuffleEnabled.value = !shuffleEnabled.value
    applyQueue(originalPlaylist.value, currentTrack.value?.id)
    persistPreferences()
  }

  function setRepeat(mode: RepeatMode): void {
    repeatMode.value = mode
    persistPreferences()
  }

  function cycleRepeat(): void {
    const index = REPEAT_ORDER.indexOf(repeatMode.value)
    setRepeat(REPEAT_ORDER[(index + 1) % REPEAT_ORDER.length])
  }

  function isCurrentTrack(trackId: string): boolean {
    return currentTrack.value?.id === trackId
  }

  if (typeof window !== 'undefined') {
    init()
  }

  return {
    currentTrack,
    originalPlaylist,
    playlist,
    currentIndex,
    status,
    currentTime,
    duration,
    volume,
    muted,
    shuffleEnabled,
    repeatMode,
    hasTrack,
    isPlaying,
    isPaused,
    progress,
    timePassed,
    timeTotal,
    trackTitle,
    hasPrevious,
    hasNext,
    volumePercent,
    init,
    play,
    pause,
    toggle,
    stop,
    seek,
    seekBy,
    seekBackward,
    seekForward,
    seekToProgress,
    setVolume,
    setVolumePercent,
    toggleMute,
    setPlaylist,
    addToPlaylist,
    clearPlaylist,
    playTrack,
    toggleTrack,
    previous,
    next,
    toggleShuffle,
    setRepeat,
    cycleRepeat,
    isCurrentTrack
  }
})
