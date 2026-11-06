import { getActivePinia, type StoreGeneric } from 'pinia'
import { disconnectRealtime } from 'src/services/realtime/echo.client'
import { useMusicPlayer } from 'src/stores/modules/musicPlayer'

const PRESERVED_STORE_IDS = new Set([
  'settings',
  'userStore',
  'musicPlayer'
])

export function resetSessionStores(): void {
  disconnectRealtime()
  useMusicPlayer().clearSession()

  const pinia = getActivePinia()
  if (!pinia) {
    return
  }

  // `_s` is omitted from Pinia's public type, so the cast goes through unknown.
  const stores = (pinia as unknown as { _s: Map<string, StoreGeneric> })._s

  stores.forEach((store) => {
    if (PRESERVED_STORE_IDS.has(store.$id)) {
      return
    }

    store.$reset()
  })
}
