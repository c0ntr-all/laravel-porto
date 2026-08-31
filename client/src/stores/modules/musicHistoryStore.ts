import { defineStore } from 'pinia'
import { ref } from 'vue'
import { historyApi } from 'src/api/requests/historyApi'
import { mapHistoryResponse } from 'src/api/mappers/Music/history.mapper'
import {
  extractCursorFromResponse,
  handleApiError,
  hasMoreFromResponse
} from 'src/utils/jsonapi'
import { IHistoryItem } from 'src/types'

export const useMusicHistoryStore = defineStore('musicHistory', () => {
  const items = ref<IHistoryItem[]>([])
  const cursor = ref<string | null>(null)
  const hasMore = ref(false)
  const isLoading = ref(false)
  const isLoadingMore = ref(false)

  let requestId = 0

  async function getHistory(options?: { append?: boolean }): Promise<void> {
    const append = Boolean(options?.append)

    if (append) {
      if (!cursor.value || isLoadingMore.value || isLoading.value) {
        return
      }
      isLoadingMore.value = true
    } else {
      requestId += 1
      isLoading.value = true
      isLoadingMore.value = false
      items.value = []
      cursor.value = null
      hasMore.value = false
    }

    const currentRequestId = requestId

    try {
      const response = await historyApi.listHistory({
        cursor: append ? cursor.value : null
      })

      if (currentRequestId !== requestId) {
        return
      }

      const mapped = mapHistoryResponse(response)
      const seen = new Set(items.value.map(item => item.id))
      items.value = append
        ? [...items.value, ...mapped.filter(item => !seen.has(item.id))]
        : mapped
      cursor.value = extractCursorFromResponse(response)
      hasMore.value = hasMoreFromResponse(response)
    } catch (error) {
      if (currentRequestId !== requestId) {
        return
      }
      handleApiError(error)
    } finally {
      if (currentRequestId === requestId) {
        isLoading.value = false
        isLoadingMore.value = false
      }
    }
  }

  return {
    items,
    cursor,
    hasMore,
    isLoading,
    isLoadingMore,
    getHistory
  }
})
