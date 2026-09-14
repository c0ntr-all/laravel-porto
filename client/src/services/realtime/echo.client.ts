import Echo from 'laravel-echo'
import Pusher from 'pusher-js'
import {
  INotificationCreatedPayload,
  INotificationReadPayload
} from 'src/types/notification'
import { IMusicUploadProgress } from 'src/types'

declare global {
  interface Window {
    Pusher: typeof Pusher
    Echo?: Echo<'reverb'>
  }
}

window.Pusher = Pusher

export type NotificationRealtimeHandlers = {
  onCreated: (payload: INotificationCreatedPayload) => void
  onRead: (payload: INotificationReadPayload) => void
  onReadAll: (payload: INotificationReadPayload) => void
}

export type MusicUploadRealtimeHandlers = {
  onStarted?: (payload: IMusicUploadProgress) => void
  onProgressed: (payload: IMusicUploadProgress) => void
  onFinished: (payload: IMusicUploadProgress) => void
}

let echo: Echo<'reverb'> | null = null
let notificationsChannel: string | null = null
const uploadChannels = new Set<string>()

function createEcho(): Echo<'reverb'> | null {
  const key = process.env.reverbKey
  const host = process.env.reverbHost

  if (!key || !host) {
    console.warn('[echo] Reverb is not configured')
    return null
  }

  const port = Number(process.env.reverbPort || 80)
  const forceTLS = process.env.reverbScheme === 'https'

  return new Echo({
    broadcaster: 'reverb',
    key,
    wsHost: host,
    wsPort: port,
    wssPort: port,
    forceTLS,
    enabledTransports: ['ws', 'wss'],
    authEndpoint: '/broadcasting/auth',
    auth: {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('access_token') ?? ''}`,
        Accept: 'application/json'
      }
    }
  })
}

function ensureEcho(): Echo<'reverb'> | null {
  if (echo) {
    return echo
  }

  echo = createEcho()
  if (echo) {
    window.Echo = echo
  }

  return echo
}

export function disconnectNotificationsRealtime(): void {
  if (echo && notificationsChannel) {
    echo.leave(notificationsChannel)
  }

  notificationsChannel = null
}

export function disconnectRealtime(): void {
  disconnectNotificationsRealtime()

  if (echo) {
    uploadChannels.forEach(channelName => echo?.leave(channelName))
    uploadChannels.clear()
    echo.disconnect()
  }

  echo = null
  window.Echo = undefined
}

export function connectNotificationsRealtime(
  userId: string | number,
  handlers: NotificationRealtimeHandlers
): () => void {
  if (echo && notificationsChannel) {
    echo.leave(notificationsChannel)
    notificationsChannel = null
  }

  const instance = ensureEcho()
  if (!instance) {
    return () => undefined
  }

  notificationsChannel = `users.${userId}.notifications`

  const channel = instance.private(notificationsChannel)
  channel.listen('.notification.created', (payload: INotificationCreatedPayload) => {
    handlers.onCreated(payload)
  })
  channel.listen('.notification.read', (payload: INotificationReadPayload) => {
    handlers.onRead(payload)
  })
  channel.listen('.notifications.read_all', (payload: INotificationReadPayload) => {
    handlers.onReadAll(payload)
  })

  return disconnectNotificationsRealtime
}

export function subscribeMusicUploadRealtime(
  uploadId: string,
  handlers: MusicUploadRealtimeHandlers
): () => void {
  const instance = ensureEcho()
  if (!instance) {
    return () => undefined
  }

  const channelName = `music.uploads.${uploadId}`
  uploadChannels.add(channelName)

  const channel = instance.private(channelName)
  if (handlers.onStarted) {
    channel.listen('.upload.started', (payload: IMusicUploadProgress) => {
      handlers.onStarted?.(payload)
    })
  }
  channel.listen('.upload.progressed', (payload: IMusicUploadProgress) => {
    handlers.onProgressed(payload)
  })
  channel.listen('.upload.finished', (payload: IMusicUploadProgress) => {
    handlers.onFinished(payload)
  })

  return () => {
    instance.leave(channelName)
    uploadChannels.delete(channelName)
  }
}
