import { api } from 'src/boot/axios'
import { IPost } from 'src/types/LifeLog/post'

interface CreatePostPayload {
  title?: string
  content?: string
  datetime: string
}

interface GetPostsResponse {
  data: IPost[]
  meta: {
    count?: number
    page?: number
    per_page?: number
  }
}

interface CreatePostResponse {
  data: IPost
  meta: {
    message?: string
  }
}

export const postApi = {
  async getPosts(): Promise<GetPostsResponse> {
    const response = await api.get('v1/lifelog/posts?sort=-datetime')
    return response.data
  },
  async createPost(payload: CreatePostPayload): Promise<CreatePostResponse> {
    const response = await api.post('v1/lifelog/posts', payload)
    return response.data
  }
}
