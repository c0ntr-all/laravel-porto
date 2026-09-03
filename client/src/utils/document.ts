import {
  ATTACHMENT_TYPES,
  DOCUMENT_EXTENSIONS,
  DOCUMENT_MIME_TYPES
} from 'src/constants/LifeLog/attachment'
import { IPostDocumentAttachment } from 'src/types/document'

export function getFileExtension(fileName: string): string {
  const parts = fileName.split('.')
  return parts.length > 1 ? parts.pop()!.toLowerCase() : ''
}

export function isDocumentMimeType(mimeType: string): boolean {
  const mime = mimeType.toLowerCase()

  if (DOCUMENT_MIME_TYPES.includes(mime as typeof DOCUMENT_MIME_TYPES[number])) {
    return true
  }

  return mime.startsWith('application/vnd.openxmlformats-officedocument')
}

export function isDocumentExtension(extension: string): boolean {
  return DOCUMENT_EXTENSIONS.includes(
    extension.toLowerCase() as typeof DOCUMENT_EXTENSIONS[number]
  )
}

export function isDocumentFile(file: File): boolean {
  if (file.type && isDocumentMimeType(file.type)) {
    return true
  }

  return isDocumentExtension(getFileExtension(file.name))
}

export function isPostDocumentAttachment(
  attachment: { attachment_type?: string }
): attachment is IPostDocumentAttachment {
  return attachment.attachment_type === ATTACHMENT_TYPES.document
}

export function formatFileSize(bytes: number): string {
  if (!Number.isFinite(bytes) || bytes <= 0) {
    return '0 B'
  }

  const units = ['B', 'KB', 'MB', 'GB']
  const exponent = Math.min(
    Math.floor(Math.log(bytes) / Math.log(1024)),
    units.length - 1
  )
  const value = bytes / 1024 ** exponent

  return `${value >= 10 || exponent === 0 ? value.toFixed(0) : value.toFixed(1)} ${units[exponent]}`
}

export function getDocumentIconName(extension: string): string {
  const normalized = extension.toLowerCase()

  if (normalized === 'pdf') return 'picture_as_pdf'
  if (['doc', 'docx', 'txt'].includes(normalized)) return 'description'
  if (['xls', 'xlsx'].includes(normalized)) return 'table_chart'
  if (['ppt', 'pptx'].includes(normalized)) return 'slideshow'
  if (['zip', 'rar', '7z'].includes(normalized)) return 'folder_zip'

  return 'insert_drive_file'
}
