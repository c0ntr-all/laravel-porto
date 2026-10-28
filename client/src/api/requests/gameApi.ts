import { api } from 'src/boot/axios'
import { IJsonApiResponse } from 'src/types'
import { IGameListQuery, IGameWriteDto } from 'src/types/Game'
import { buildFilterForUrl } from 'src/utils/jsonapi'

export const gameApi = {
  async getGames(query?: IGameListQuery): Promise<IJsonApiResponse> {
    const filters = buildFilterForUrl({
      name: query?.name
    })
    const response = await api.get(filters ? `v1/games?${filters}` : 'v1/games')

    return response.data
  },

  async getGame(id: string): Promise<IJsonApiResponse> {
    const response = await api.get(`v1/games/${id}`)

    return response.data
  },

  async updateGame(id: string, payload: IGameWriteDto): Promise<IJsonApiResponse> {
    const response = await api.patch(`v1/games/${id}`, payload)

    return response.data
  },

  async deleteGame(id: string): Promise<IJsonApiResponse> {
    const response = await api.delete(`v1/games/${id}`)

    return response.data
  }
}
