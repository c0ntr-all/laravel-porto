import { api } from 'src/boot/axios'
import { IFilter, IJsonApiResponse, IPresetCreateDto } from 'src/types'
import { buildFilterForUrl } from 'src/utils/jsonapi'

export const presetApi = {
  async getPresets(filters: IFilter): Promise<IJsonApiResponse> {
    const defaultSort: string = '-date'
    let url: string = `v1/lifelog/presets?sort=${defaultSort}`
    if (filters) {
      const spatieFilters = buildFilterForUrl(filters)
      url += `&${spatieFilters}`
    }
    const response = await api.get(url)

    return response.data
  },
  async createPreset(payload: IPresetCreateDto): Promise<IJsonApiResponse> {
    const response = await api.post('v1/lifelog/presets', payload)
    return response.data
  },

  async getPreset(id: string): Promise<IJsonApiResponse> {
    const response = await api.get(`v1/lifelog/presets/${id}`)
    return response.data
  },

  async updatePreset(id: string, payload: IPresetCreateDto): Promise<IJsonApiResponse> {
    const response = await api.patch(`v1/lifelog/presets/${id}`, payload)
    return response.data
  },

  async deletePreset(id: string): Promise<IJsonApiResponse> {
    const response = await api.delete(`v1/lifelog/presets/${id}`)
    return response.data
  }
}
