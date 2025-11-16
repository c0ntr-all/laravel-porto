import { defineStore } from 'pinia'
import { ref } from 'vue'
import { postApi } from 'src/api/requests/LifeLog/postApi'
import { useAttachmentStore } from 'src/stores/modules/attachmentStore'
import { IPost, IPostFilter, IPostModel, IPostUpdateModel } from 'src/types/LifeLog/post'
import { handleApiError, handleApiSuccess } from 'src/utils/jsonapi'
import { mapResponse } from 'src/utils/jsonApiMapper'
import { mapPostFormToCreateDto, mapPostFormToUpdateDto } from 'src/api/mappers/post.mapper'
import { IPostUpdateDto } from 'src/api/DTO/PostUpdateDto'
import { IJsonApiResponse } from 'src/types'
import { IPostCreateDto } from 'src/api/DTO/PostCreateDto'

export const usePostStore = defineStore('post', () => {
  const posts = ref<IPost[]>([])
  const postsCount = ref<number>(0)
  const isLoading = ref<boolean>(false)
  const error = ref<string | null>(null)

  async function getPosts(filters: IPostFilter = {}) {
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
      const postCreateDto: IPostCreateDto = mapPostFormToCreateDto(postModel)
      const responseData: IJsonApiResponse = await postApi.createPost(postCreateDto)
      const mappedResponse: IPost[] = mapResponse(responseData) as IPost[]
      const newPost: IPost = mappedResponse[0]

      if (attachmentModel?.length) {
        const attachmentStore = useAttachmentStore()
        const newAttachments = await attachmentStore.createAttachment({
          files: attachmentModel,
          attachable_type: newPost.type,
          attachable_id: newPost.id,
          correlation_uuid: responseData.meta?.correlation_uuid
        })

        if (newAttachments) {
          newPost.attachments = newAttachments
        }
      }

      posts.value.unshift(newPost)
      postsCount.value += 1

      handleApiSuccess(responseData)

      return newPost
    } catch (err: any) {
      handleApiError(err)
      error.value = err.message ?? 'Ошибка создания'
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
      const postUpdateDto: IPostUpdateDto = mapPostFormToUpdateDto(postModel, originalPost)
      const responseData: IJsonApiResponse = await postApi.updatePost(id, postUpdateDto)
      const updatedPost: IPost = mapResponse(responseData)[0] as IPost

      if (attachmentModel?.length) {
        const attachmentStore = useAttachmentStore()
        const uploadedAttachments = await attachmentStore.createAttachment({
          files: attachmentModel,
          attachable_type: updatedPost.type,
          attachable_id: updatedPost.id
        })
        if (uploadedAttachments) {
          updatedPost.attachments.push(...uploadedAttachments)
        }
      }

      // add to main list of posts
      const index = posts.value.findIndex(p => p.id === updatedPost.id)
      if (index !== -1) {
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
