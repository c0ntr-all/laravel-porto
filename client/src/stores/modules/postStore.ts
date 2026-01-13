import { defineStore } from 'pinia'
import { ref } from 'vue'
import { postApi } from 'src/api/requests/postApi'
import { IPost, IPostFilter, IPostModel, IPostUpdateModel } from 'src/types/LifeLog/post'
import { handleApiError, handleApiSuccess } from 'src/utils/jsonapi'
import { mapResponse } from 'src/utils/jsonApiMapper'
import { mapPostFormToCreateDto, mapPostFormToUpdateDto } from 'src/api/mappers/post.mapper'
import { IPostUpdateDto } from 'src/api/DTO/PostUpdateDto'
import { IJsonApiResponse } from 'src/types'
import { IPostCreateDto } from 'src/api/DTO/PostCreateDto'
import { uploadPostAttachments } from 'src/services/post.service'

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

      let attachmentsIds: string[] | undefined = []
      if (attachmentModel?.length) {
        attachmentsIds = await uploadPostAttachments(attachmentModel)
      }
      postCreateDto.attachments = attachmentsIds

      const responseData: IJsonApiResponse = await postApi.createPost(postCreateDto)
      const mappedResponse: IPost[] = mapResponse(responseData) as IPost[]
      const newPost: IPost = mappedResponse[0]

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

      let attachmentsIds: string[] | undefined = []
      if (attachmentModel?.length) {
        attachmentsIds = await uploadPostAttachments(attachmentModel)
      }
      postUpdateDto.attachments = attachmentsIds

      const responseData: IJsonApiResponse = await postApi.updatePost(id, postUpdateDto)
      const updatedPost: IPost = mapResponse(responseData)[0] as IPost

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
