import { defineStore } from 'pinia'
import { ref } from 'vue'
import { gameApi } from 'src/api/requests/gameApi'
import { mapGameResponse, mapGamesResponse } from 'src/api/mappers/Game/game.mapper'
import { IGame } from 'src/types/Game'
import { notifyGameApiError } from 'src/utils/game'

export const useGameStore = defineStore('games', () => {
  const games = ref<IGame[]>([])
  const game = ref<IGame | null>(null)
  const gamesTotal = ref<number | null>(null)
  const isGamesLoading = ref(false)
  const isGameLoading = ref(false)
  const listName = ref('')

  let listRequestId = 0

  function upsertGame(incoming: IGame): void {
    const index = games.value.findIndex(item => item.id === incoming.id)

    if (index === -1) {
      games.value = [incoming, ...games.value]

      if (gamesTotal.value != null) {
        gamesTotal.value += 1
      }
    } else {
      games.value.splice(index, 1, incoming)
    }

    if (game.value?.id === incoming.id) {
      game.value = incoming
    }
  }

  function removeGame(id: string): void {
    const existed = games.value.some(item => item.id === id)
    games.value = games.value.filter(item => item.id !== id)

    if (existed && gamesTotal.value != null) {
      gamesTotal.value = Math.max(gamesTotal.value - 1, 0)
    }

    if (game.value?.id === id) {
      game.value = null
    }
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

  async function getGame(id: string): Promise<IGame | null> {
    const listed = games.value.find(item => item.id === id)

    if (game.value?.id !== id) {
      game.value = listed ?? null
    }

    isGameLoading.value = true

    try {
      const response = await gameApi.getGame(id)
      game.value = mapGameResponse(response)

      return game.value
    } catch (error) {
      notifyGameApiError(error)
      return listed ?? null
    } finally {
      isGameLoading.value = false
    }
  }

  return {
    games,
    game,
    gamesTotal,
    isGamesLoading,
    isGameLoading,
    listName,
    getGames,
    getGame,
    upsertGame,
    removeGame
  }
})
