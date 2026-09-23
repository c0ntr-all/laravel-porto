import { defineStore } from 'pinia'
import { ref } from 'vue'
import { postApi } from 'src/api/requests/postApi'
import {
  extractCursorFromResponse,
  handleApiError,
  handleApiSuccess,
  hasMoreFromResponse
} from 'src/utils/jsonapi'
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

function mergePostsById(current: IPost[], incoming: IPost[]): IPost[] {
  const seen = new Set(current.map(post => post.id))

  return [...current, ...incoming.filter(post => !seen.has(post.id))]
}

export const usePostStore = defineStore('post', () => {
  const posts = ref<IPost[]>([])
  const postsCount = ref<number>(0)
  const postsCursor = ref<string | null>(null)
  const hasMorePosts = ref(false)
  const isLoading = ref<boolean>(false)
  const isLoadingMore = ref<boolean>(false)
  const error = ref<string | null>(null)

  let listRequestId = 0
  let lastListFilters: IFilter = {}

  async function getPosts(options?: {
    append?: boolean
    filters?: IFilter
  }) {
    const append = Boolean(options?.append)

    if (options?.filters) {
      lastListFilters = { ...options.filters }
    }

    if (append) {
      if (!postsCursor.value || isLoadingMore.value || isLoading.value) {
        return
      }

      isLoadingMore.value = true
    } else {
      listRequestId += 1
      isLoading.value = true
      isLoadingMore.value = false
      posts.value = []
      postsCursor.value = null
      hasMorePosts.value = false
    }

    const requestId = listRequestId
    error.value = null

    try {
      const response = await postApi.getPosts(lastListFilters, {
        cursor: append ? postsCursor.value : null
      })

      if (requestId !== listRequestId) {
        return
      }

      const mapped = normalizePosts(mapResponse(response) as IPost[])
      const previousCount = posts.value.length

      posts.value = append ? mergePostsById(posts.value, mapped) : mapped
      postsCursor.value = extractCursorFromResponse(response)
      hasMorePosts.value = hasMoreFromResponse(response)

      if (append && posts.value.length === previousCount) {
        hasMorePosts.value = false
      }

      postsCount.value = posts.value.length
    } catch (err: any) {
      if (requestId !== listRequestId) {
        return
      }

      error.value = err.message ?? 'Ошибка загрузки'
    } finally {
      if (requestId === listRequestId) {
        isLoading.value = false
        isLoadingMore.value = false
      }
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
    postsCursor,
    hasMorePosts,
    isLoading,
    isLoadingMore,
    getPosts,
    createPost,
    updatePost
  }
})
