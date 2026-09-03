import Echo from 'laravel-echo'
import Pusher from 'pusher-js'
import {
  INotificationCreatedPayload,
  INotificationReadPayload
} from 'src/types/notification'

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

let echo: Echo<'reverb'> | null = null
let subscribedChannel: string | null = null

function apiOrigin(): string {
  return (process.env.host ?? '').replace(/\/$/, '')
}

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
    authEndpoint: `${apiOrigin()}/broadcasting/auth`,
    auth: {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('access_token') ?? ''}`,
        Accept: 'application/json'
      }
    }
  })
}

export function disconnectNotificationsRealtime(): void {
  if (echo && subscribedChannel) {
    echo.leave(subscribedChannel)
  }

  echo?.disconnect()
  echo = null
  subscribedChannel = null
  window.Echo = undefined
}

export function connectNotificationsRealtime(
  userId: string | number,
  handlers: NotificationRealtimeHandlers
): () => void {
  disconnectNotificationsRealtime()

  echo = createEcho()
  if (!echo) {
    return () => undefined
  }

  window.Echo = echo
  subscribedChannel = `users.${userId}.notifications`

  const channel = echo.private(subscribedChannel)
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
