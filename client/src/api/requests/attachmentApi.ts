import { api } from 'src/boot/axios'
import { ApiRequestContext, IJsonApiResponse } from 'src/types'
import { buildCorrelationHeaders } from 'src/utils/correlation'

interface UploadAttachmentsOptions {
  onProgress?: (percent: number) => void
}

export const attachmentApi = {
  async upload(
    attachableType: string,
    attachableId: string,
    files: File[],
    ctx?: ApiRequestContext,
    options: UploadAttachmentsOptions = {}
  ): Promise<IJsonApiResponse> {
    const formData = new FormData()
    formData.append('attachable_type', attachableType)
    formData.append('attachable_id', attachableId)

    files.forEach(file => {
      formData.append('files[]', file)
    })

    const response = await api.post('v1/app/attachments/upload', formData, {
      headers: buildCorrelationHeaders(ctx),
      onUploadProgress: event => {
        if (!event.total || !options.onProgress) {
          return
        }

        options.onProgress(Math.round((event.loaded * 100) / event.total))
      }
    })

    return response.data
  }
}
