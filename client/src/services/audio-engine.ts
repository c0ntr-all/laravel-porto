export type AudioEngineEventMap = {
  timeupdate: { currentTime: number; duration: number }
  durationchange: { duration: number }
  progress: { buffered: AudioBufferedRange[]; duration: number }
  ended: undefined
  play: undefined
  pause: undefined
  waiting: undefined
  playing: undefined
  error: { message: string }
  volumechange: { volume: number; muted: boolean }
}

export type AudioBufferedRange = {
  start: number
  end: number
}

type AudioEngineHandler<K extends keyof AudioEngineEventMap> = (
  payload: AudioEngineEventMap[K]
) => void

function safeDuration(audio: HTMLAudioElement): number {
  return Number.isFinite(audio.duration) ? audio.duration : 0
}

function readBuffered(audio: HTMLAudioElement): AudioBufferedRange[] {
  const ranges: AudioBufferedRange[] = []

  try {
    for (let index = 0; index < audio.buffered.length; index++) {
      ranges.push({
        start: audio.buffered.start(index),
        end: audio.buffered.end(index)
      })
    }
  } catch {
    return ranges
  }

  return ranges
}

export class AudioEngine {
  private audio: HTMLAudioElement | null = null
  private loadToken = 0
  private pendingSeek: number | null = null
  private listeners: { [K in keyof AudioEngineEventMap]: Set<AudioEngineHandler<K>> } = {
    timeupdate: new Set(),
    durationchange: new Set(),
    progress: new Set(),
    ended: new Set(),
    play: new Set(),
    pause: new Set(),
    waiting: new Set(),
    playing: new Set(),
    error: new Set(),
    volumechange: new Set()
  }

  on<K extends keyof AudioEngineEventMap>(event: K, handler: AudioEngineHandler<K>): void {
    this.listeners[event].add(handler)
  }

  off<K extends keyof AudioEngineEventMap>(event: K, handler: AudioEngineHandler<K>): void {
    this.listeners[event].delete(handler)
  }

  get currentTime(): number {
    return this.pendingSeek ?? this.audio?.currentTime ?? 0
  }

  get duration(): number {
    return this.audio ? safeDuration(this.audio) : 0
  }

  get buffered(): AudioBufferedRange[] {
    return this.audio ? readBuffered(this.audio) : []
  }

  get volume(): number {
    return this.audio?.volume ?? 1
  }

  get muted(): boolean {
    return this.audio?.muted ?? false
  }

  get paused(): boolean {
    return this.audio?.paused ?? true
  }

  get src(): string {
    return this.audio?.src ?? ''
  }

  load(src: string): void {
    const audio = this.ensureAudio()
    this.loadToken += 1
    this.pendingSeek = null
    audio.pause()
    audio.src = src
    audio.currentTime = 0
    audio.load()
    this.emitProgress(audio)
  }

  async play(): Promise<void> {
    const audio = this.ensureAudio()
    const token = this.loadToken

    try {
      await audio.play()
    } catch (error) {
      if (token !== this.loadToken) {
        return
      }

      throw error
    }
  }

  pause(): void {
    this.audio?.pause()
  }

  stop(): void {
    if (!this.audio) {
      return
    }

    this.pendingSeek = null
    this.audio.pause()
    this.audio.currentTime = 0
  }

  seek(time: number): void {
    if (!Number.isFinite(time)) {
      return
    }

    const audio = this.ensureAudio()
    const duration = safeDuration(audio)
    const nextTime = duration > 0
      ? Math.min(Math.max(time, 0), duration)
      : Math.max(time, 0)

    this.pendingSeek = nextTime
    this.emit('timeupdate', {
      currentTime: nextTime,
      duration
    })
    this.applyPendingSeek(audio)
  }

  seekBy(delta: number): void {
    this.seek(this.currentTime + delta)
  }

  setVolume(volume: number): void {
    const audio = this.ensureAudio()
    audio.volume = Math.min(Math.max(volume, 0), 1)
  }

  setMuted(muted: boolean): void {
    const audio = this.ensureAudio()
    audio.muted = muted
  }

  private applyPendingSeek(audio: HTMLAudioElement): boolean {
    if (this.pendingSeek === null) {
      return true
    }

    if (audio.readyState < HTMLMediaElement.HAVE_METADATA) {
      return false
    }

    const duration = safeDuration(audio)
    const nextTime = duration > 0
      ? Math.min(Math.max(this.pendingSeek, 0), duration)
      : this.pendingSeek

    try {
      audio.currentTime = nextTime
    } catch {
      return false
    }

    return true
  }

  private emitProgress(audio: HTMLAudioElement): void {
    this.emit('progress', {
      buffered: readBuffered(audio),
      duration: safeDuration(audio)
    })
  }

  private shouldIgnoreTimeUpdate(currentTime: number): boolean {
    if (this.pendingSeek === null) {
      return false
    }

    return Math.abs(currentTime - this.pendingSeek) > 0.35
  }

  private ensureAudio(): HTMLAudioElement {
    if (this.audio) {
      return this.audio
    }

    const audio = new Audio()
    audio.preload = 'auto'
    audio.addEventListener('timeupdate', () => {
      if (this.shouldIgnoreTimeUpdate(audio.currentTime)) {
        this.applyPendingSeek(audio)
        return
      }

      if (this.pendingSeek !== null && Math.abs(audio.currentTime - this.pendingSeek) <= 0.35) {
        this.pendingSeek = null
      }

      this.emit('timeupdate', {
        currentTime: audio.currentTime,
        duration: safeDuration(audio)
      })
      this.emitProgress(audio)
    })
    audio.addEventListener('durationchange', () => {
      this.applyPendingSeek(audio)
      this.emit('durationchange', { duration: safeDuration(audio) })
    })
    audio.addEventListener('loadedmetadata', () => {
      this.applyPendingSeek(audio)
      this.emit('durationchange', { duration: safeDuration(audio) })
      this.emitProgress(audio)
    })
    audio.addEventListener('progress', () => this.emitProgress(audio))
    audio.addEventListener('canplay', () => {
      this.applyPendingSeek(audio)
      this.emitProgress(audio)
    })
    audio.addEventListener('seeked', () => {
      this.pendingSeek = null
      this.emit('timeupdate', {
        currentTime: audio.currentTime,
        duration: safeDuration(audio)
      })
      this.emitProgress(audio)
    })
    audio.addEventListener('ended', () => {
      this.pendingSeek = null
      this.emit('ended', undefined)
    })
    audio.addEventListener('play', () => this.emit('play', undefined))
    audio.addEventListener('pause', () => this.emit('pause', undefined))
    audio.addEventListener('waiting', () => this.emit('waiting', undefined))
    audio.addEventListener('playing', () => this.emit('playing', undefined))
    audio.addEventListener('volumechange', () => {
      this.emit('volumechange', {
        volume: audio.volume,
        muted: audio.muted
      })
    })
    audio.addEventListener('error', () => {
      const mediaError = audio.error
      this.emit('error', {
        message: mediaError?.message || 'Unable to play this track'
      })
    })

    this.audio = audio
    return audio
  }

  private emit<K extends keyof AudioEngineEventMap>(
    event: K,
    payload: AudioEngineEventMap[K]
  ): void {
    this.listeners[event].forEach(handler => handler(payload))
  }
}

export const audioEngine = new AudioEngine()
