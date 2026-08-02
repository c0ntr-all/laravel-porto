import { defineStore } from 'pinia'
import { ref } from 'vue'
import { postApi } from 'src/api/requests/postApi'
import { handleApiError, handleApiSuccess } from 'src/utils/jsonapi'
import { mapResponse } from 'src/utils/jsonApiMapper'
import {
  createPostWithAttachments,
  updatePostWithAttachments
} from 'src/services/post-create.service'
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
      posts.value = mapResponse(response) as IPost[]
      postsCount.value = response.meta?.count || 0
    } catch (err: any) {
      error.value = err.message ?? 'Ошибка загрузки'
    } finally {
      isLoading.value = false
    }
  }

  async function createPost(postModel: IPostModel, attachmentModel: File[]): Promise<IPost> {
    try {
      const { post, response } = await createPostWithAttachments(postModel, attachmentModel)

      posts.value.unshift(post)
      postsCount.value += 1
      handleApiSuccess(response)

      return post
    } catch (err: any) {
      handleApiError(err.message || 'Не удалось создать пост')
      throw err
    }
  }

  async function updatePost(
    id: string,
    postModel: IPostUpdateModel,
    originalPost: IPost,
    // TODO: Переименовать attachmentModel т.к. есть тип IAttachmentModel, а тут просто файлы
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
        posts.value.splice(index, 1, post)
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
