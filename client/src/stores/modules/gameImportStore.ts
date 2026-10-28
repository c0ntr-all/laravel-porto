import { defineStore } from 'pinia'
import { ref } from 'vue'
import { AxiosError } from 'axios'
import { Notify } from 'quasar'
import { gameImportApi } from 'src/api/requests/gameImportApi'
import { mapGameImportResponse, mapGameImportsResponse } from 'src/api/mappers/Game/game.mapper'
import { extractCursorFromResponse, handleApiSuccess, hasMoreFromResponse } from 'src/utils/jsonapi'
import { notifyGameApiError } from 'src/utils/game'
import { useGameAdminStore } from 'src/stores/modules/gameAdminStore'
import { GameImportStatusEnum } from 'src/enums/Game/GameImportStatusEnum'
import { IJsonApiResponse } from 'src/types'
import { IGameImport } from 'src/types/Game'

function mergeById(current: IGameImport[], incoming: IGameImport[]): IGameImport[] {
  const seen = new Set(current.map(item => item.id))

  return [...current, ...incoming.filter(item => !seen.has(item.id))]
}

function importResponseFromError(error: unknown): IJsonApiResponse | null {
  if (!(error instanceof AxiosError) || !error.response?.data) {
    return null
  }

  const data = error.response.data as IJsonApiResponse
  const resource = Array.isArray(data.data) ? data.data[0] : data.data

  if (resource?.type !== 'game_imports') {
    return null
  }

  return data
}

export const useGameImportStore = defineStore('gameImports', () => {
  const imports = ref<IGameImport[]>([])
  const importsCursor = ref<string | null>(null)
  const hasMoreImports = ref(false)
  const isLoading = ref(false)
  const isLoadingMore = ref(false)
  const isImporting = ref(false)
  const lastImport = ref<IGameImport | null>(null)

  let listRequestId = 0

  function upsertImport(incoming: IGameImport): void {
    lastImport.value = incoming
    const index = imports.value.findIndex(item => item.id === incoming.id)

    if (index === -1) {
      imports.value = [incoming, ...imports.value]
      return
    }

    imports.value.splice(index, 1, incoming)
  }

  function applyImportedGame(imported: IGameImport): void {
    if (!imported.game || imported.status !== GameImportStatusEnum.COMPLETED) {
      return
    }

    useGameAdminStore().upsertImportedGame(imported.game)
  }

  async function getImports(options?: { append?: boolean }): Promise<void> {
    const append = Boolean(options?.append)

    if (append) {
      if (!importsCursor.value || isLoadingMore.value || isLoading.value) {
        return
      }

      isLoadingMore.value = true
    } else {
      listRequestId += 1
      isLoading.value = true
      isLoadingMore.value = false
      imports.value = []
      importsCursor.value = null
      hasMoreImports.value = false
    }

    const requestId = listRequestId

    try {
      const response = await gameImportApi.getImports({
        cursor: append ? importsCursor.value : null
      })

      if (requestId !== listRequestId) {
        return
      }

      const mapped = mapGameImportsResponse(response)
      imports.value = append ? mergeById(imports.value, mapped) : mapped
      importsCursor.value = extractCursorFromResponse(response)
      hasMoreImports.value = hasMoreFromResponse(response)
    } catch (error) {
      if (requestId !== listRequestId) {
        return
      }

      notifyGameApiError(error)
    } finally {
      if (requestId === listRequestId) {
        isLoading.value = false
        isLoadingMore.value = false
      }
    }
  }

  async function importFromSteam(steamId: number): Promise<IGameImport | null> {
    isImporting.value = true

    try {
      const response = await gameImportApi.importGame(steamId)
      const mapped = mapGameImportResponse(response)

      upsertImport(mapped)
      applyImportedGame(mapped)
      handleApiSuccess(response)

      return mapped
    } catch (error) {
      const failedResponse = importResponseFromError(error)

      if (failedResponse) {
        const mapped = mapGameImportResponse(failedResponse)

        upsertImport(mapped)
        Notify.create({
          type: 'negative',
          message: mapped.error_message || failedResponse.meta?.message || 'Steam import failed'
        })

        return mapped
      }

      notifyGameApiError(error)
      return null
    } finally {
      isImporting.value = false
    }
  }

  return {
    imports,
    importsCursor,
    hasMoreImports,
    isLoading,
    isLoadingMore,
    isImporting,
    lastImport,
    getImports,
    importFromSteam
  }
})
