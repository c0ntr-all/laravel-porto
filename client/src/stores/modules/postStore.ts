import { defineStore } from 'pinia'
import { ref } from 'vue'
import { postApi } from 'src/api/requests/postApi'
import { handleApiError, handleApiSuccess } from 'src/utils/jsonapi'
import { mapResponse } from 'src/utils/jsonApiMapper'
import { mapPostFormToCreateDto, mapPostFormToUpdateDto } from 'src/api/mappers/post.mapper'
import { IPostUpdateDto } from 'src/api/DTO/PostUpdateDto'
import { IPostCreateDto } from 'src/api/DTO/PostCreateDto'
import { uploadPostAttachments } from 'src/services/post.service'
import {
  IJsonApiResponse,
  IPost,
  IFilter,
  IPostModel,
  IPostUpdateModel,
  IPeriod,
  IPeriodModel, IPeriodCreateDto
} from 'src/types'
import { mapPeriodFormModelToCreateDto } from 'src/api/mappers/LifeLog/period.mapper'

export const usePostStore = defineStore('post', () => {
  const posts = ref<IPost[]>([])
  const postsCount = ref<number>(0)
  const periods = ref<IPost[]>([])
  const periodsCount = ref<number>(0)
  const isLoading = ref<boolean>(false)
  const error = ref<string | null>(null)
  const startPeriodPostId = ref<string | null>(null)
  const endPeriodPostId = ref<string | null>(null)

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
    const postCreateDto: IPostCreateDto = mapPostFormToCreateDto(postModel)

    let attachmentsIds: {id: string, type: string}[] | undefined = []
    if (attachmentModel?.length) {
      try {
        attachmentsIds = await uploadPostAttachments(attachmentModel)
      } catch (error: any) {
        const errorMessage = error.message || 'Не удалось загрузить вложения'
        handleApiError(errorMessage)

        throw new Error(`Ошибка загрузки вложений: ${errorMessage}`)
      }
    }

    postCreateDto.attachments = attachmentsIds

    try {
      const responseData: IJsonApiResponse = await postApi.createPost(postCreateDto)
      const mappedResponse: IPost[] = mapResponse(responseData) as IPost[]
      const newPost: IPost = mappedResponse[0]

      posts.value.unshift(newPost)
      postsCount.value += 1

      handleApiSuccess(responseData)

      return newPost
    } catch (error: any) {
      handleApiError(error.message || 'Не удалось создать пост')
      throw error
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

      let attachmentsIds: {id: string, type: string}[] | undefined = []
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

  function setStartPeriodPostId(id: string|null) {
    startPeriodPostId.value = id
  }

  function setEndPeriodPostId(id: string|null) {
    endPeriodPostId.value = id
  }

  async function createPeriod(periodModel: IPeriodModel): Promise<IPeriod> {
    const periodCreateDto: IPeriodCreateDto = mapPeriodFormModelToCreateDto(periodModel)

    try {
      const responseData: IJsonApiResponse = await postApi.createPeriod(periodCreateDto)
      const mappedResponse: IPeriod[] = mapResponse(responseData) as IPeriod[]
      const newPeriod: IPeriod = mappedResponse[0]

      periods.value.unshift(newPeriod)
      periodsCount.value += 1

      handleApiSuccess(responseData)

      return newPeriod
    } catch (error: any) {
      handleApiError(error.message || 'Не удалось создать период')
      throw error
    }
  }

  return {
    posts,
    postsCount,
    isLoading,
    startPeriodPostId,
    endPeriodPostId,
    getPosts,
    createPost,
    updatePost,
    setStartPeriodPostId,
    setEndPeriodPostId,
    createPeriod
  }
})
