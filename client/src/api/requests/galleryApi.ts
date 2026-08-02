import { api } from 'src/boot/axios'
import { ApiRequestContext, IJsonApiResponse } from 'src/types'
import { mapMediaItemToFormData } from 'src/api/mappers/gallery.mapper'
import { buildCorrelationHeaders } from 'src/utils/correlation'

export const galleryApi = {
  async upload(
    url: string,
    file: File,
    onProgress: (percent: number) => void,
    ctx?: ApiRequestContext
  ): Promise<IJsonApiResponse> {
    const formData = mapMediaItemToFormData(file)

    const response = await api.post(
      url,
      formData,
      {
        headers: buildCorrelationHeaders(ctx),
        onUploadProgress: (event) => {
          if (!event.total) return
          onProgress(Math.round(event.loaded * 100 / event.total))
        }
      }
    )

    return response.data
  }
}
