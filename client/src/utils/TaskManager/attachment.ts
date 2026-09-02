import { isDocumentFile } from 'src/utils/document'
import {
  TASK_IMAGE_MIME_TYPES,
  TASK_MAX_FILE_SIZE_KB,
  TASK_VIDEO_MIME_TYPES
} from 'src/constants/TaskManager/attachment'

export function isTaskSupportedFile(file: File): boolean {
  const mime = file.type.toLowerCase()

  if (TASK_IMAGE_MIME_TYPES.includes(mime as typeof TASK_IMAGE_MIME_TYPES[number])) {
    return true
  }

  if (TASK_VIDEO_MIME_TYPES.includes(mime as typeof TASK_VIDEO_MIME_TYPES[number])) {
    return true
  }

  return isDocumentFile(file)
}

export function isTaskFileTooLarge(file: File): boolean {
  return file.size > TASK_MAX_FILE_SIZE_KB * 1024
}

export function splitTaskFiles(files: File[]): {
  supported: File[]
  unsupported: File[]
  tooLarge: File[]
} {
  const supported: File[] = []
  const unsupported: File[] = []
  const tooLarge: File[] = []

  for (const file of files) {
    if (!isTaskSupportedFile(file)) {
      unsupported.push(file)
      continue
    }

    if (isTaskFileTooLarge(file)) {
      tooLarge.push(file)
      continue
    }

    supported.push(file)
  }

  return { supported, unsupported, tooLarge }
}
