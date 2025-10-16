import { defineStore } from 'pinia'
import { ref } from 'vue'
import { handleApiSuccess } from 'src/utils/jsonapi'
import { mapResponse } from 'src/utils/jsonApiMapper'
import { IJsonApiResponse } from 'src/types'
import { mapAttachmentModelToCreateDto } from 'src/api/mappers/attachment.mapper'
import { IAttachment } from 'src/types/attachment'
import { attachmentApi } from 'src/api/requests/attachmentApi'

export const useAttachmentStore = defineStore('attachment', () => {
  const isLoading = ref<boolean>(false)
  const error = ref<string | null>(null)

  async function createAttachment(attachmentModel) {
    try {
      const attachmentCreateDto = mapAttachmentModelToCreateDto(attachmentModel)
      const responseData = await attachmentApi.createAttachment<IJsonApiResponse>(attachmentCreateDto)
      const mappedResponse = mapResponse(responseData) as IAttachment[]

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
