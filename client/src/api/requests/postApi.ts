import { api } from 'src/boot/axios'
import { ApiRequestContext, IFilter, IJsonApiResponse } from 'src/types'
import { IPostCreateDto } from 'src/api/DTO/PostCreateDto'
import { IPostUpdateDto } from 'src/api/DTO/PostUpdateDto'
import { buildFilterForUrl } from 'src/utils/jsonapi'
import { buildCorrelationHeaders } from 'src/utils/correlation'

export const postApi = {
  async getPosts(filters: IFilter): Promise<IJsonApiResponse> {
    const defaultSort: string = '-date'
    let url: string = `v1/lifelog/posts?sort=${defaultSort}`
    if (filters) {
      const spatieFilters = buildFilterForUrl(filters)
      url += `&${spatieFilters}`
    }
    const response = await api.get(url)

    return response.data
  },
  async createPost(
    postCreateDto: IPostCreateDto,
    ctx?: ApiRequestContext
  ): Promise<IJsonApiResponse> {
    const response = await api.post('v1/lifelog/posts', postCreateDto, {
      headers: buildCorrelationHeaders(ctx)
    })
    return response.data
  },
  async updatePost(
    id: string,
    postUpdateDto: IPostUpdateDto,
    ctx?: ApiRequestContext
  ): Promise<IJsonApiResponse> {
    const response = await api.patch(`v1/lifelog/posts/${id}`, postUpdateDto, {
      headers: buildCorrelationHeaders(ctx)
    })
    return response.data
  }
}
