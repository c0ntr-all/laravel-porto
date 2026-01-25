import { api } from 'src/boot/axios'
import { IJsonApiResponse } from 'src/types'
import { mapMediaItemToFormData } from 'src/api/mappers/gallery.mapper'

export const galleryApi = {
  async upload(
    url: string,
    file: File,
    onProgress: (percent: number) => void
  ): Promise<IJsonApiResponse> {
    const formData = mapMediaItemToFormData(file)

    const response = await api.post(
      url,
      formData,
      {
        onUploadProgress: (event) => {
          if (!event.total) return
          onProgress(Math.round(event.loaded * 100 / event.total))
        }
      }
    )

    return response.data
  }
}
