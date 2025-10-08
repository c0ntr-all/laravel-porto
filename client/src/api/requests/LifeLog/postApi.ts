import { api } from 'src/boot/axios'
import { IJsonApiResponse } from 'src/types'
import { IPostCreateDto } from 'src/api/DTO/PostCreateDto'
import { IPostUpdateDto } from 'src/api/DTO/PostUpdateDto'

export const postApi = {
  async getPosts(): Promise<IJsonApiResponse> {
    const response = await api.get('v1/lifelog/posts?sort=-datetime')
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
