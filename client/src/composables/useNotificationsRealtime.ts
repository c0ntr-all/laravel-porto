import { onUnmounted, watch } from 'vue'
import { storeToRefs } from 'pinia'
import { useNotificationStore } from 'src/stores/modules/notificationStore'
import { useUserStore } from 'src/stores/modules/userStore'
import { connectNotificationsRealtime, disconnectNotificationsRealtime } from 'src/services/realtime/echo.client'

export function useNotificationsRealtime() {
  const userStore = useUserStore()
  const notificationStore = useNotificationStore()
  const { isLoggedIn, user } = storeToRefs(userStore)

  function startSession(userId: string): void {
    disconnectNotificationsRealtime()
    connectNotificationsRealtime(userId, {
      onCreated: payload => notificationStore.applyCreated(payload),
      onRead: payload => notificationStore.applyRead(payload),
      onReadAll: payload => notificationStore.applyReadAll(payload)
    })
    void notificationStore.bootstrap()
  }

  function stopSession(): void {
    disconnectNotificationsRealtime()
    notificationStore.reset()
  }

  watch(
    () => [isLoggedIn.value, user.value.id] as const,
    ([loggedIn, userId]) => {
      if (loggedIn && userId) {
        startSession(userId)
        return
      }

      stopSession()
    },
    { immediate: true }
  )

  onUnmounted(() => {
    stopSession()
  })

  return {
    startSession,
    stopSession
  }
}
