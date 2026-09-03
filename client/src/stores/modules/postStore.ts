import { defineStore } from 'pinia'
import { ref } from 'vue'
import { postApi } from 'src/api/requests/postApi'
import { handleApiError, handleApiSuccess } from 'src/utils/jsonapi'
import { mapResponse } from 'src/utils/jsonApiMapper'
import { normalizePost, normalizePosts } from 'src/api/mappers/post.response.mapper'
import {
  createPostWithAttachments,
  updatePostWithAttachments
} from 'src/services/post-create.service'
import {
  buildOptimisticPost,
  insertPostByDate,
  replaceOptimisticPost
} from 'src/utils/LifeLog/post'
import { useUserStore } from 'src/stores/modules/userStore'
import {
  IFilter,
  IPost,
  IPostModel,
  IPostUpdateModel
} from 'src/types'

export const usePostStore = defineStore('post', () => {
  const posts = ref<IPost[]>([])
  const postsCount = ref<number>(0)
  const isLoading = ref<boolean>(false)
  const error = ref<string | null>(null)

  async function getPosts(filters: IFilter = {}) {
    isLoading.value = true
    error.value = null

    try {
      const response = await postApi.getPosts(filters)
      posts.value = normalizePosts(mapResponse(response) as IPost[])
      postsCount.value = response.meta?.count || 0
    } catch (err: any) {
      error.value = err.message ?? 'Ошибка загрузки'
    } finally {
      isLoading.value = false
    }
  }

  async function createPost(postModel: IPostModel, attachmentModel: File[]): Promise<IPost> {
    const userStore = useUserStore()
    const optimisticPost = buildOptimisticPost(postModel, userStore.user)
    const optimisticId = optimisticPost.id

    insertPostByDate(posts.value, optimisticPost)
    postsCount.value += 1

    try {
      const { post, response } = await createPostWithAttachments(postModel, attachmentModel)
      const normalizedPost = normalizePost(post)

      replaceOptimisticPost(posts.value, optimisticId, normalizedPost)
      handleApiSuccess(response)

      return normalizedPost
    } catch (err: any) {
      const optimisticIndex = posts.value.findIndex(post => post.id === optimisticId)

      if (optimisticIndex !== -1) {
        posts.value.splice(optimisticIndex, 1)
        postsCount.value = Math.max(0, postsCount.value - 1)
      }

      handleApiError(err.message || 'Не удалось создать пост')
      throw err
    }
  }

  async function updatePost(
    id: string,
    postModel: IPostUpdateModel,
    originalPost: IPost,
    attachmentModel: File[]
  ) {
    if (!postModel || !originalPost) return

    try {
      const { post, response } = await updatePostWithAttachments(
        id,
        postModel,
        originalPost,
        attachmentModel
      )

      const index = posts.value.findIndex(p => p.id === post.id)

      if (index !== -1) {
        posts.value.splice(index, 1, normalizePost(post))
      }

      handleApiSuccess(response)

      return post
    } catch (err: any) {
      error.value = err.message ?? 'Error while updating post!'
      handleApiError(err.message ?? 'Error while updating post!')
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
