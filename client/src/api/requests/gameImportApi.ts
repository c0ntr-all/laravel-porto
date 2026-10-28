import { api } from 'src/boot/axios'
import { IJsonApiResponse } from 'src/types'
import { IGameImportListQuery } from 'src/types/Game'
import { buildFilterForUrl } from 'src/utils/jsonapi'

export const gameImportApi = {
  async importGame(steamId: number): Promise<IJsonApiResponse> {
    const response = await api.post(
      'v1/games/imports',
      { steam_id: steamId },
      { timeout: 120000 }
    )

    return response.data
  },

  async getImports(query?: IGameImportListQuery): Promise<IJsonApiResponse> {
    const filters = buildFilterForUrl({
      steam_id: query?.steam_id,
      status: query?.status
    })
    const response = await api.get(
      filters ? `v1/games/imports?${filters}` : 'v1/games/imports',
      {
        params: {
          include: 'game',
          ...(query?.cursor ? { cursor: query.cursor } : {})
        }
      }
    )

    return response.data
  }
}
