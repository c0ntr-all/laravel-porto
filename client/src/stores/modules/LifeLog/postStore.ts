import { defineStore } from 'pinia'
import { ref } from 'vue'
import { postApi } from 'src/api/requests/LifeLog/postApi'
import { IPost } from 'src/types/LifeLog/post'
import { handleApiSuccess } from 'src/utils/jsonapi'
import { mapResponse } from 'src/utils/jsonApiMapper'
import { mapPostFormToCreateDto, mapPostFormToUpdateDto } from 'src/api/mappers/post.mapper'
import { IPostUpdateDto } from 'src/api/DTO/PostUpdateDto'
import { IJsonApiResponse } from 'src/types'

export const usePostStore = defineStore('post', () => {
  const posts = ref<IPost[]>([])
  const postsCount = ref<number>(0)
  const isLoading = ref<boolean>(false)
  const error = ref<string | null>(null)

  async function getPosts() {
    isLoading.value = true
    error.value = null
    try {
      const response = await postApi.getPosts()
      posts.value = mapResponse(response) as IPost[]
      postsCount.value = response.meta?.count || 0
    } catch (err: any) {
      error.value = err.message ?? 'Ошибка загрузки'
    } finally {
      isLoading.value = false
    }
  }

  async function createPost(postModel) {
    try {
      const postCreateDto = mapPostFormToCreateDto(postModel)
      const responseData = await postApi.createPost(postCreateDto)
      const mappedResponse = mapResponse(responseData) as IPost[]
      posts.value.unshift(...mappedResponse)
      postsCount.value += 1

      handleApiSuccess(responseData)

      return mappedResponse
    } catch (err: any) {
      error.value = err.message ?? 'Ошибка создания'
    }
  }

  async function updatePost(id: string, postModel, originalPost) {
    if (!postModel) return

    try {
      const postUpdateDto: IPostUpdateDto = mapPostFormToUpdateDto(originalPost, postModel)
      const responseData: IJsonApiResponse = await postApi.updatePost(id, postUpdateDto)
      const updatedPost: IPost[] = mapResponse(responseData)[0] as IPost

      const index = posts.value.findIndex(p => p.id === updatedPost.id)
      if (index !== -1) {
        // ✅ реактивный способ обновления
        posts.value.splice(index, 1, updatedPost)
      }

      handleApiSuccess(responseData)

      return updatedPost
    } catch (err: any) {
      error.value = err.message ?? 'Error while updating post!'
    }
  }

  return {
    posts,
    postsCount,
    isLoading,
    getPosts,
    createPost,
    updatePost
  }
})
