import { nanoid } from 'nanoid'
import { IUploadItem } from 'src/types/gallery'
import { TagsModeEnum } from 'src/enums/upload/UploadStatusEnum'

export function mapMediaItemToFormData(file: File): FormData {
  const formData = new FormData()
  formData.append('file', file)

  return formData
}

export function mapFileToUploadItem(file: File): IUploadItem {
  return {
    id: nanoid(),
    file,
    progress: 0,
    status: TagsModeEnum.PENDING
  }
}
