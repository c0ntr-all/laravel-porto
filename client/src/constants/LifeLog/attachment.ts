export const LL_POST_ATTACHABLE_TYPE = 'll_posts'

export const ATTACHMENT_TYPES = {
  image: 'gallery_images',
  video: 'gallery_videos',
  document: 'app_documents'
} as const

export const DOCUMENT_MIME_TYPES = [
  'application/pdf',
  'application/msword',
  'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
  'application/zip',
  'application/x-zip-compressed',
  'application/vnd.rar',
  'application/x-rar-compressed',
  'application/x-7z-compressed',
  'text/plain',
  'application/vnd.ms-excel',
  'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
  'application/vnd.ms-powerpoint',
  'application/vnd.openxmlformats-officedocument.presentationml.presentation'
] as const

export const DOCUMENT_EXTENSIONS = [
  'pdf',
  'doc',
  'docx',
  'zip',
  'rar',
  '7z',
  'txt',
  'xls',
  'xlsx',
  'ppt',
  'pptx'
] as const

export const POST_FILE_ACCEPT = [
  'image/*',
  'video/*',
  ...DOCUMENT_MIME_TYPES,
  ...DOCUMENT_EXTENSIONS.map(ext => `.${ext}`)
].join(',')
