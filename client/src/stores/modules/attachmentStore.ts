import { defineStore } from 'pinia'
import { ref } from 'vue'
import { handleApiSuccess } from 'src/utils/jsonapi'
import { mapResponse } from 'src/utils/jsonApiMapper'
import { mapAttachmentModelToCreateDto } from 'src/api/mappers/attachment.mapper'
import { IAttachment, IAttachmentModel } from 'src/types/attachment'
import { attachmentApi } from 'src/api/requests/attachmentApi'
import { IJsonApiResponse } from 'src/types'

export const useAttachmentStore = defineStore('attachment', () => {
  const isLoading = ref<boolean>(false)
  const error = ref<string | null>(null)

  async function createAttachment(attachmentModel: IAttachmentModel) {
    try {
      const attachmentCreateDto: FormData = mapAttachmentModelToCreateDto(attachmentModel)
      const responseData: IJsonApiResponse = await attachmentApi.createAttachment(attachmentCreateDto)
      const mappedResponse: IAttachment[] = mapResponse(responseData) as IAttachment[]

      handleApiSuccess(responseData)

      return mappedResponse
    } catch (err: any) {
      error.value = err.message ?? 'Ошибка создания'
    }
  }

  return {
    isLoading,
    error,
    createAttachment
  }
})
