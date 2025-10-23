import { api } from 'src/boot/axios'
import { IJsonApiResponse } from 'src/types'

export const attachmentApi = {
  async createAttachment(attachmentCreateDto: FormData): Promise<IJsonApiResponse> {
    const response = await api.post('v1/app/attachments/upload', attachmentCreateDto)
    return response.data
  }
}
