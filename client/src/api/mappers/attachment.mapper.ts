import { IAttachmentModel } from 'src/types/attachment'

export function mapAttachmentModelToCreateDto(attachmentModel: IAttachmentModel): FormData {
  const formData = new FormData()

  formData.append('attachable_type', attachmentModel.attachable_type)
  formData.append('attachable_id', attachmentModel.attachable_id)
  if (attachmentModel.correlation_uuid) {
    formData.append('correlation_uuid', attachmentModel.correlation_uuid)
  }
  attachmentModel.files.forEach((file: File) => {
    formData.append('files[]', file)
  })

  return formData
}
