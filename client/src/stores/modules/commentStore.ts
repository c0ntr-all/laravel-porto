import { defineStore } from 'pinia'
import { reactive } from 'vue'
import { mapCommentResponse, mapCommentsResponse } from 'src/api/mappers/comment.mapper'
import { commentApi } from 'src/api/requests/commentApi'
import { usePostStore } from 'src/stores/modules/postStore'
import { IGalleryComment } from 'src/types/gallery'
import { handleApiError, handleApiSuccess } from 'src/utils/jsonapi'

const POST_COMMENTABLE_TYPE = 'll_posts'

function cacheKey(type: string, id: string): string {
  return `${type}:${id}`
}

function syncPostCommentsCount(type: string, id: string, count: number): void {
  if (type !== POST_COMMENTABLE_TYPE) {
    return
  }

  usePostStore().setCommentsCount(id, count)
}

export const useCommentStore = defineStore('comments', () => {
  const commentsByKey = reactive<Record<string, IGalleryComment[]>>({})
  const loadingByKey = reactive<Record<string, boolean>>({})
  const loadFailedByKey = reactive<Record<string, boolean>>({})
  const sendingByKey = reactive<Record<string, boolean>>({})
  const loadRequestIds: Record<string, number> = {}

  async function loadComments(
    type: string,
    id: string,
    options?: { sort?: string }
  ): Promise<void> {
    const key = cacheKey(type, id)
    const requestId = (loadRequestIds[key] ?? 0) + 1
    loadRequestIds[key] = requestId
    loadingByKey[key] = true
    loadFailedByKey[key] = false

    try {
      const filters = {
        commentable_id: id,
        commentable_type: type
      }
      const response = options?.sort === undefined
        ? await commentApi.getComments(filters)
        : await commentApi.getComments(filters, options.sort)

      if (loadRequestIds[key] !== requestId) {
        return
      }

      const list = mapCommentsResponse(response)

      if (options?.sort === 'created_at') {
        list.sort((left, right) => left.created_at.localeCompare(right.created_at))
      }

      commentsByKey[key] = list
      syncPostCommentsCount(type, id, list.length)
    } catch (error) {
      if (loadRequestIds[key] !== requestId) {
        return
      }

      handleApiError(error)
      loadFailedByKey[key] = true
      commentsByKey[key] = []
    } finally {
      if (loadRequestIds[key] === requestId) {
        loadingByKey[key] = false
      }
    }
  }

  async function createComment(
    type: string,
    id: string,
    content: string,
    options?: { placement: 'start' | 'end', notifySuccess?: boolean }
  ): Promise<IGalleryComment | null> {
    const key = cacheKey(type, id)
    sendingByKey[key] = true

    try {
      const response = await commentApi.createComment({
        commentable_id: id,
        commentable_type: type,
        content
      })
      const created = mapCommentResponse(response)
      const current = commentsByKey[key] ?? []
      const placement = options?.placement ?? 'end'

      commentsByKey[key] = placement === 'start'
        ? [created, ...current]
        : [...current, created]
      syncPostCommentsCount(type, id, commentsByKey[key].length)

      if (options?.notifySuccess) {
        handleApiSuccess(response)
      }

      return created
    } catch (error) {
      handleApiError(error)

      return null
    } finally {
      sendingByKey[key] = false
    }
  }

  async function updateComment(
    type: string,
    id: string,
    commentId: string,
    content: string
  ): Promise<IGalleryComment | null> {
    const key = cacheKey(type, id)

    try {
      const response = await commentApi.updateComment(commentId, content)
      const updated = mapCommentResponse(response)
      const current = commentsByKey[key] ?? []

      commentsByKey[key] = current.map(item => item.id === updated.id ? updated : item)

      return updated
    } catch (error) {
      handleApiError(error)

      return null
    }
  }

  async function deleteComment(
    type: string,
    id: string,
    commentId: string
  ): Promise<boolean> {
    const key = cacheKey(type, id)

    try {
      await commentApi.deleteComment(commentId)
      const current = commentsByKey[key] ?? []
      const next = current.filter(item => item.id !== commentId)

      commentsByKey[key] = next
      syncPostCommentsCount(type, id, next.length)

      return true
    } catch (error) {
      handleApiError(error)

      return false
    }
  }

  return {
    commentsByKey,
    loadingByKey,
    loadFailedByKey,
    sendingByKey,
    loadComments,
    createComment,
    updateComment,
    deleteComment
  }
})
