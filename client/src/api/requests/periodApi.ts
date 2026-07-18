import { api } from 'src/boot/axios'
import { IFilter, IJsonApiResponse, IPeriodCreateDto } from 'src/types'
import { buildFilterForUrl } from 'src/utils/jsonapi'

export const periodApi = {
  async getPeriods(filters: IFilter): Promise<IJsonApiResponse> {
    const defaultSort: string = '-date'
    let url: string = `v1/lifelog/periods?sort=${defaultSort}`
    if (filters) {
      const spatieFilters = buildFilterForUrl(filters)
      url += `&${spatieFilters}`
    }
    const response = await api.get(url)

    return response.data
  },
  async createPeriod(payload: IPeriodCreateDto): Promise<IJsonApiResponse> {
    const response = await api.post('v1/lifelog/periods', payload)
    return response.data
  }
}
