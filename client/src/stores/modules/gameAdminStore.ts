import { defineStore } from 'pinia'
import { ref } from 'vue'
import { gameApi } from 'src/api/requests/gameApi'
import { mapGameResponse, mapGamesResponse } from 'src/api/mappers/Game/game.mapper'
import { handleApiSuccess } from 'src/utils/jsonapi'
import { notifyGameApiError } from 'src/utils/game'
import { IGame, IGameWriteDto } from 'src/types/Game'
import { useGameStore } from 'src/stores/modules/gameStore'

export const useGameAdminStore = defineStore('gameAdmin', () => {
  const games = ref<IGame[]>([])
  const gamesTotal = ref<number | null>(null)
  const isGamesLoading = ref(false)
  const isSaving = ref(false)
  const listName = ref('')

  let listRequestId = 0

  function syncPublicStore(incoming: IGame): void {
    useGameStore().upsertGame(incoming)
  }

  function removeFromPublicStore(id: string): void {
    useGameStore().removeGame(id)
  }

  function upsertGame(incoming: IGame): void {
    const index = games.value.findIndex(item => item.id === incoming.id)

    if (index === -1) {
      games.value = [incoming, ...games.value]

      if (gamesTotal.value != null) {
        gamesTotal.value += 1
      }

      return
    }

    games.value.splice(index, 1, incoming)
  }

  function upsertImportedGame(incoming: IGame): void {
    upsertGame(incoming)
    syncPublicStore(incoming)
  }

  async function getGames(options?: { name?: string }): Promise<void> {
    if (options && 'name' in options) {
      listName.value = options.name?.trim() ?? ''
    }

    listRequestId += 1
    isGamesLoading.value = true
    const requestId = listRequestId

    try {
      const response = await gameApi.getGames({
        name: listName.value || undefined
      })

      if (requestId !== listRequestId) {
        return
      }

      games.value = mapGamesResponse(response)
      gamesTotal.value = typeof response.meta?.count === 'number'
        ? response.meta.count
        : games.value.length
    } catch (error) {
      if (requestId !== listRequestId) {
        return
      }

      notifyGameApiError(error)
    } finally {
      if (requestId === listRequestId) {
        isGamesLoading.value = false
      }
    }
  }

  async function createGame(payload: IGameWriteDto): Promise<IGame | null> {
    isSaving.value = true

    try {
      const response = await gameApi.createGame(payload)
      const created = mapGameResponse(response)

      upsertGame(created)
      syncPublicStore(created)
      handleApiSuccess(response)

      return created
    } catch (error) {
      notifyGameApiError(error)
      return null
    } finally {
      isSaving.value = false
    }
  }

  async function updateGame(id: string, payload: IGameWriteDto): Promise<IGame | null> {
    isSaving.value = true

    try {
      const response = await gameApi.updateGame(id, payload)
      const updated = mapGameResponse(response)

      upsertGame(updated)
      syncPublicStore(updated)
      handleApiSuccess(response)

      return updated
    } catch (error) {
      notifyGameApiError(error)
      return null
    } finally {
      isSaving.value = false
    }
  }

  async function deleteGame(id: string): Promise<boolean> {
    isSaving.value = true

    try {
      const response = await gameApi.deleteGame(id)
      const existed = games.value.some(item => item.id === id)
      games.value = games.value.filter(item => item.id !== id)

      if (existed && gamesTotal.value != null) {
        gamesTotal.value = Math.max(gamesTotal.value - 1, 0)
      }

      removeFromPublicStore(id)
      handleApiSuccess(response)

      return true
    } catch (error) {
      notifyGameApiError(error)
      return false
    } finally {
      isSaving.value = false
    }
  }

  return {
    games,
    gamesTotal,
    isGamesLoading,
    isSaving,
    listName,
    getGames,
    createGame,
    updateGame,
    deleteGame,
    upsertImportedGame
  }
})
