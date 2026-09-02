import { api } from 'src/boot/axios'
import { IHistoryListQuery, IJsonApiResponse } from 'src/types'

export const historyApi = {
  async listHistory(query?: IHistoryListQuery): Promise<IJsonApiResponse> {
    const response = await api.get('v1/music/history', {
      params: {
        include: 'track,track.artists,track.album',
        ...(query?.cursor ? { cursor: query.cursor } : {})
      }
    })

    return response.data
  }
}
