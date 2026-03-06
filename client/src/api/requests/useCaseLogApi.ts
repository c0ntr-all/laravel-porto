import { api } from 'src/boot/axios'
import { IUseCaseLogGetResponse, IFilter } from 'src/types'
import { buildFilterForUrl } from 'src/utils/jsonapi'

export const useCaseLogApi = {
  async getUseCaseLogs(filters: IFilter = {}): Promise<IUseCaseLogGetResponse> {
    const defaultSort: string = '-created_at'
    let url: string = `v1/use-case-logs?sort=${defaultSort}`

    if (filters) {
      const spatieFilters = buildFilterForUrl(filters)
      url += `&${spatieFilters}`
    }

    const response = await api.get(url)

    return response.data
  }
}
