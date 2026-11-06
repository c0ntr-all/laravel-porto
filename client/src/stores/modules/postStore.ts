import { defineStore } from 'pinia'
import { ref } from 'vue'
import { postApi } from 'src/api/requests/postApi'
import { handleApiError, handleApiSuccess, isRequestCanceled } from 'src/utils/jsonapi'
import { createListRequest, mergeCursorPage, withListRequest } from 'src/utils/listRequest'
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
import { postMatchesApiListFilter } from 'src/utils/LifeLog/filter.mapper'
import { useUserStore } from 'src/stores/modules/userStore'
import {
  IFilter,
  IPost,
  IPostModel,
  IPostUpdateModel,
  IRelationshipItem
} from 'src/types'

export const usePostStore = defineStore('post', () => {
  const posts = ref<IPost[]>([])
  const postsCount = ref<number>(0)
  const postsCursor = ref<string | null>(null)
  const hasMorePosts = ref(false)
  const isLoading = ref<boolean>(false)
  const isLoadingMore = ref<boolean>(false)
  const error = ref<string | null>(null)

  const listRequest = createListRequest()
  let lastListFilters: IFilter = {}

  function syncPostWithListFilters(post: IPost): void {
    const matches = postMatchesApiListFilter(post, lastListFilters)
    const index = posts.value.findIndex(item => item.id === post.id)

    if (!matches) {
      if (index !== -1) {
        posts.value.splice(index, 1)
        postsCount.value = Math.max(0, postsCount.value - 1)
      }
      return
    }

    if (index !== -1) {
      posts.value.splice(index, 1)
    }

    insertPostByDate(posts.value, post)
    postsCount.value = posts.value.length
  }

  async function getPosts(options?: {
    append?: boolean
    filters?: IFilter
  }) {
    const append = Boolean(options?.append)

    if (options?.filters) {
      lastListFilters = { ...options.filters }
    }

    await withListRequest(listRequest, append, {
      canAppend: Boolean(postsCursor.value) && !isLoadingMore.value && !isLoading.value,
      onAppend: () => {
        isLoadingMore.value = true
      },
      onFresh: () => {
        isLoading.value = true
        isLoadingMore.value = false
        posts.value = []
        postsCursor.value = null
        hasMorePosts.value = false
      },
      onSettle: () => {
        isLoading.value = false
        isLoadingMore.value = false
      }
    }, async (isCurrent) => {
      error.value = null

      try {
        const response = await postApi.getPosts(lastListFilters, {
          cursor: append ? postsCursor.value : null
        })

        if (!isCurrent()) {
          return
        }

        const mapped = normalizePosts(mapResponse(response) as IPost[])
        const previousCount = posts.value.length
        const page = mergeCursorPage(posts.value, mapped, response, append)

        posts.value = page.items
        postsCursor.value = page.cursor
        hasMorePosts.value = page.hasMore

        if (append && posts.value.length === previousCount) {
          hasMorePosts.value = false
        }

        postsCount.value = posts.value.length
      } catch (err: unknown) {
        if (!isCurrent() || isRequestCanceled(err)) {
          return
        }

        error.value = err instanceof Error ? err.message : 'Ошибка загрузки'
      }
    })
  }

  async function createPost(
    postModel: IPostModel,
    attachmentModel: File[],
    galleryRefs: IRelationshipItem[] = []
  ): Promise<IPost> {
    const userStore = useUserStore()
    const optimisticPost = buildOptimisticPost(postModel, userStore.user)
    const optimisticId = optimisticPost.id
    const shouldShowOptimistic = postMatchesApiListFilter(optimisticPost, lastListFilters)

    if (shouldShowOptimistic) {
      insertPostByDate(posts.value, optimisticPost)
      postsCount.value += 1
    }

    try {
      const { post, response } = await createPostWithAttachments(
        postModel,
        attachmentModel,
        galleryRefs
      )
      const normalizedPost = normalizePost(post)

      if (shouldShowOptimistic) {
        replaceOptimisticPost(posts.value, optimisticId, normalizedPost)
        if (!postMatchesApiListFilter(normalizedPost, lastListFilters)) {
          const index = posts.value.findIndex(item => item.id === normalizedPost.id)
          if (index !== -1) {
            posts.value.splice(index, 1)
          }
          postsCount.value = posts.value.length
        }
      } else {
        syncPostWithListFilters(normalizedPost)
      }

      handleApiSuccess(response)

      return normalizedPost
    } catch (err: any) {
      if (shouldShowOptimistic) {
        const optimisticIndex = posts.value.findIndex(post => post.id === optimisticId)

        if (optimisticIndex !== -1) {
          posts.value.splice(optimisticIndex, 1)
          postsCount.value = Math.max(0, postsCount.value - 1)
        }
      }

      handleApiError(err.message || 'Не удалось создать пост')
      throw err
    }
  }

  async function updatePost(
    id: string,
    postModel: IPostUpdateModel,
    originalPost: IPost,
    attachmentModel: File[],
    galleryRefs: IRelationshipItem[] = []
  ) {
    if (!postModel || !originalPost) return

    try {
      const { post, response } = await updatePostWithAttachments(
        id,
        postModel,
        originalPost,
        attachmentModel,
        galleryRefs
      )

      syncPostWithListFilters(normalizePost(post))
      handleApiSuccess(response)

      return post
    } catch (err: any) {
      error.value = err.message ?? 'Error while updating post!'
      handleApiError(err.message ?? 'Error while updating post!')
    }
  }

  function setCommentsCount(postId: string, count: number): void {
    const post = posts.value.find(item => item.id === postId)
    if (!post) {
      return
    }

    post.comments_count = Math.max(0, count)
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
    updatePost,
    setCommentsCount
  }
})
