import { IAttachmentModel } from 'src/types/attachment'

export function mapAttachmentModelToCreateDto(attachmentModel: IAttachmentModel): FormData {
  const formData = new FormData()

  formData.append('attachable_type', attachmentModel.attachable_type)
  formData.append('attachable_id', attachmentModel.attachable_id)
  attachmentModel.files.forEach((file: File) => {
    formData.append('files[]', file)
  })

  return formData
}
