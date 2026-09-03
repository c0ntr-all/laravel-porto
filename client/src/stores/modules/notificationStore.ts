import { computed, ref } from 'vue'
import { defineStore } from 'pinia'
import { notificationApi } from 'src/api/requests/notificationApi'
import {
  emptyNotificationListMeta,
  mapNotification,
  mapNotificationList,
  mapRealtimeNotification
} from 'src/api/mappers/notification.mapper'
import { NOTIFICATION_INBOX_SIZE, NOTIFICATION_PAGE_SIZE } from 'src/constants/notifications'
import {
  INotification,
  INotificationCreatedPayload,
  INotificationListMeta,
  INotificationReadPayload
} from 'src/types/notification'
import { handleApiError } from 'src/utils/jsonapi'
import { mapResponse } from 'src/utils/jsonApiMapper'

function upsertFront(list: INotification[], item: INotification): INotification[] {
  const without = list.filter(entry => entry.id !== item.id)
  return [item, ...without]
}

function markItemRead(item: INotification): INotification {
  if (item.is_read) {
    return item
  }

  return {
    ...item,
    is_read: true,
    read_at: item.read_at || new Date().toISOString()
  }
}

export const useNotificationStore = defineStore('notificationStore', () => {
  const unreadCount = ref(0)
  const inbox = ref<INotification[]>([])
  const items = ref<INotification[]>([])
  const meta = ref<INotificationListMeta>(emptyNotificationListMeta())
  const isInboxOpen = ref(false)
  const isInboxLoading = ref(false)
  const isListLoading = ref(false)
  const highlightedId = ref<string | null>(null)

  const badgeLabel = computed(() => {
    if (unreadCount.value <= 0) {
      return ''
    }

    return unreadCount.value > 99 ? '99+' : String(unreadCount.value)
  })

  function setUnreadCount(count: number): void {
    unreadCount.value = Math.max(0, count)
  }

  function reset(): void {
    unreadCount.value = 0
    inbox.value = []
    items.value = []
    meta.value = emptyNotificationListMeta()
    isInboxOpen.value = false
    isInboxLoading.value = false
    isListLoading.value = false
    highlightedId.value = null
  }

  async function fetchUnreadCount(): Promise<void> {
    const payload = await notificationApi.unreadCount()
    setUnreadCount(payload.unread_count)
  }

  async function bootstrap(): Promise<void> {
    try {
      await fetchUnreadCount()
    } catch {
      // Badge is best-effort; auth errors are handled by the axios interceptor.
    }
  }

  async function fetchInbox(): Promise<void> {
    isInboxLoading.value = true

    try {
      const result = mapNotificationList(await notificationApi.list({
        unread_only: true,
        per_page: NOTIFICATION_INBOX_SIZE,
        page: 1
      }))
      inbox.value = result.items
    } catch (error) {
      handleApiError(error)
    } finally {
      isInboxLoading.value = false
    }
  }

  async function fetchPage(page = 1, perPage = NOTIFICATION_PAGE_SIZE): Promise<void> {
    isListLoading.value = true

    try {
      const result = mapNotificationList(await notificationApi.list({
        page,
        per_page: perPage
      }))
      items.value = result.items
      meta.value = result.meta
    } catch (error) {
      handleApiError(error)
    } finally {
      isListLoading.value = false
    }
  }

  async function openInbox(): Promise<void> {
    isInboxOpen.value = true
    await fetchInbox()

    if (inbox.value.length === 0 && unreadCount.value === 0) {
      return
    }

    try {
      await notificationApi.markAllAsRead()
      setUnreadCount(0)
      inbox.value = inbox.value.map(markItemRead)
      items.value = items.value.map(markItemRead)
    } catch (error) {
      handleApiError(error)
    }
  }

  function closeInbox(): void {
    isInboxOpen.value = false
  }

  function setHighlightedId(id: string | null): void {
    highlightedId.value = id
  }

  function applyCreated(payload: INotificationCreatedPayload): void {
    const notification = mapRealtimeNotification(payload)

    if (isInboxOpen.value) {
      inbox.value = upsertFront(inbox.value, markItemRead(notification))
      void notificationApi.markAsRead(notification.id).catch(() => undefined)
    } else {
      setUnreadCount(payload.unread_count)
    }

    if (meta.value.current_page === 1) {
      const exists = items.value.some(item => item.id === notification.id)
      items.value = upsertFront(items.value, {
        ...notification,
        is_read: isInboxOpen.value ? true : notification.is_read
      })

      if (!exists) {
        meta.value = {
          ...meta.value,
          total: meta.value.total + 1,
          last_page: Math.max(1, Math.ceil((meta.value.total + 1) / meta.value.per_page))
        }
      }
    }
  }

  function applyRead(payload: INotificationReadPayload): void {
    setUnreadCount(payload.unread_count)

    if (!payload.notification_id) {
      return
    }

    const id = payload.notification_id
    inbox.value = inbox.value.map(item => (item.id === id ? markItemRead(item) : item))
    items.value = items.value.map(item => (item.id === id ? markItemRead(item) : item))
  }

  function applyReadAll(payload: INotificationReadPayload): void {
    setUnreadCount(payload.unread_count)
    inbox.value = inbox.value.map(markItemRead)
    items.value = items.value.map(markItemRead)
  }

  async function markOneRead(id: string): Promise<void> {
    try {
      const payload = await notificationApi.markAsRead(id)
      const mapped = mapResponse(payload).map(item => mapNotification(item))[0]
      if (mapped) {
        inbox.value = inbox.value.map(item => (item.id === id ? mapped : item))
        items.value = items.value.map(item => (item.id === id ? mapped : item))
      } else {
        applyRead({ notification_id: id, unread_count: Math.max(0, unreadCount.value - 1) })
      }
    } catch (error) {
      handleApiError(error)
    }
  }

  return {
    unreadCount,
    inbox,
    items,
    meta,
    isInboxOpen,
    isInboxLoading,
    isListLoading,
    highlightedId,
    badgeLabel,
    reset,
    bootstrap,
    fetchInbox,
    fetchPage,
    openInbox,
    closeInbox,
    setHighlightedId,
    applyCreated,
    applyRead,
    applyReadAll,
    markOneRead
  }
})
