import { api } from 'src/boot/axios'
import { IJsonApiResponse } from 'src/types'
import { IAttachmentCreateDto } from 'src/api/DTO/AttachmentCreateDto'

export const attachmentApi = {
  async createAttachment(attachmentCreateDto: IAttachmentCreateDto): Promise<IJsonApiResponse> {
    const response = await api.post('v1/app/attachments/upload', attachmentCreateDto)
    return response.data
  }
}
