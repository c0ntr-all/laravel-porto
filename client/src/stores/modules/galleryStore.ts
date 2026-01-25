import { defineStore } from 'pinia'
import { ref } from 'vue'
import { handleApiSuccess } from 'src/utils/jsonapi'
import { IJsonApiResponse } from 'src/types'
import { galleryApi } from 'src/api/requests/galleryApi'
import { mapResponse } from 'src/utils/jsonApiMapper'
import { IUploadItem } from 'src/types/gallery'

export const useGalleryStore = defineStore('gallery', () => {
  const isLoading = ref<boolean>(false)
  const error = ref<string | null>(null)

  async function uploadFiles(url: string, items: IUploadItem[]) {
    try {
      const result = []
      for (const item of items) {
        const responseData: IJsonApiResponse = await galleryApi.upload(
          url,
          item.file,
          (progress: number): void => { item.progress = progress }
        )
        const mappedResponse = mapResponse(responseData)

        result.push(mappedResponse[0])

        handleApiSuccess(responseData)
      }

      return result
    } catch (err: any) {
      error.value = err.message ?? 'Ошибка создания'
    }
  }

  return {
    isLoading,
    error,
    uploadFiles
  }
})
