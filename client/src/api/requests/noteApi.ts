import { api } from 'src/boot/axios'
import { IJsonApiResponse, INoteCreatePayload, INoteListQuery, INoteUpdatePayload } from 'src/types'

function listUrl (query: INoteListQuery = {}): string {
  const params = new URLSearchParams()

  if (query.cursor) params.set('cursor', query.cursor)
  if (query.perPage) params.set('per_page', String(query.perPage))
  if (query.sort) params.set('sort', query.sort)

  const text = query.text?.trim()
  if (text) params.set('filter[text]', text)
  if (query.parentId != null) params.set('filter[parent_id]', String(query.parentId))
  if (query.pinned === 0 || query.pinned === 1) params.set('filter[pinned]', String(query.pinned))
  if (query.roots) params.set('filter[roots]', '1')

  const qs = params.toString()
  return qs ? `v1/notes?${qs}` : 'v1/notes'
}

export const noteApi = {
  async list (query?: INoteListQuery): Promise<IJsonApiResponse> {
    const response = await api.get(listUrl(query))
    return response.data
  },

  async get (id: string): Promise<IJsonApiResponse> {
    const response = await api.get(`v1/notes/${id}`)
    return response.data
  },

  async create (payload: INoteCreatePayload): Promise<IJsonApiResponse> {
    const response = await api.post('v1/notes', payload)
    return response.data
  },

  async update (id: string, payload: INoteUpdatePayload): Promise<IJsonApiResponse> {
    const response = await api.patch(`v1/notes/${id}`, payload)
    return response.data
  },

  async remove (id: string): Promise<void> {
    await api.delete(`v1/notes/${id}`)
  },

  async pin (id: string): Promise<IJsonApiResponse> {
    const response = await api.post(`v1/notes/${id}/pin`)
    return response.data
  },

  async unpin (id: string): Promise<IJsonApiResponse> {
    const response = await api.delete(`v1/notes/${id}/pin`)
    return response.data
  },

  async movePin (id: string, beforeId: number | null): Promise<IJsonApiResponse> {
    const response = await api.post(`v1/notes/${id}/pin-position`, { before_id: beforeId })
    return response.data
  }
}
