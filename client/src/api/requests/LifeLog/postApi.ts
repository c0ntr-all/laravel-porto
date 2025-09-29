import { api } from 'src/boot/axios'
import { IJsonApiResponse } from 'src/types'
import { IPostCreateDto } from 'src/api/DTO/PostCreateDto'

export const postApi = {
  async getPosts(): Promise<IJsonApiResponse> {
    const response = await api.get('v1/lifelog/posts?sort=-datetime')
    return response.data
  },
  async createPost(postCreateDto: IPostCreateDto): Promise<IJsonApiResponse> {
    const response = await api.post('v1/lifelog/posts', postCreateDto)
    return response.data
  }
}
