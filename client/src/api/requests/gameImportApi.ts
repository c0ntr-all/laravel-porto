import { api } from 'src/boot/axios'
import { IJsonApiResponse } from 'src/types'

export const gameImportApi = {
  async importGame(steamId: number): Promise<IJsonApiResponse> {
    const response = await api.post(
      'v1/games/import',
      { steam_id: steamId },
      { timeout: 120000 }
    )

    return response.data
  }
}
