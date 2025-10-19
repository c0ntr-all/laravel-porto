import { api } from 'src/boot/axios'
import { IJsonApiResponse, IPostFilter } from 'src/types'
import { IPostCreateDto } from 'src/api/DTO/PostCreateDto'
import { IPostUpdateDto } from 'src/api/DTO/PostUpdateDto'
import { buildFilterForUrl } from 'src/utils/jsonapi'

export const postApi = {
  async getPosts(filters: IPostFilter): Promise<IJsonApiResponse> {
    const defaultSort: string = '-datetime'
    let url: string = `v1/lifelog/posts?sort=${defaultSort}`
    if (filters) {
      const spatieFilters = buildFilterForUrl(filters)
      url += `&${spatieFilters}`
    }
    const response = await api.get(url)

    return response.data
  },
  async createPost(postCreateDto: IPostCreateDto): Promise<IJsonApiResponse> {
    const response = await api.post('v1/lifelog/posts', postCreateDto)
    return response.data
  },
  async updatePost(id: string, postUpdateDto: IPostUpdateDto): Promise<IJsonApiResponse> {
    const response = await api.patch(`v1/lifelog/posts/${id}`, postUpdateDto)
    return response.data
  }
}
