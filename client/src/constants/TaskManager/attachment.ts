import { DOCUMENT_EXTENSIONS, DOCUMENT_MIME_TYPES } from 'src/constants/LifeLog/attachment'

export const TM_TASK_ATTACHABLE_TYPE = 'tm_tasks'

export const TASK_IMAGE_MIME_TYPES = [
  'image/jpeg',
  'image/png',
  'image/gif',
  'image/webp'
] as const

export const TASK_VIDEO_MIME_TYPES = [
  'video/mp4'
] as const

export const TASK_MAX_FILE_SIZE_KB = 20480

export const TASK_FILE_ACCEPT = [
  ...TASK_IMAGE_MIME_TYPES,
  ...TASK_VIDEO_MIME_TYPES,
  ...DOCUMENT_MIME_TYPES,
  ...DOCUMENT_EXTENSIONS.map(ext => `.${ext}`)
].join(',')
