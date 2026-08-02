import { galleryApi } from 'src/api/requests/galleryApi'
import { mapFileToUploadItem } from 'src/api/mappers/gallery.mapper'
import { mapResponse } from 'src/utils/jsonApiMapper'
import { mergeCorrelationFromResponse } from 'src/utils/correlation'
import { ApiRequestContext, IRelationshipItem } from 'src/types'

const DEFAULT_ALBUM_ID = 1

interface IFileGroups {
  images: File[]
  videos: File[]
  documents: File[]
  music: File[]
  other: File[]
}

export interface UploadAttachmentsResult {
  ids: IRelationshipItem[]
  context: ApiRequestContext
}

async function uploadFilesToUrl(
  url: string,
  files: File[],
  ctx: ApiRequestContext
): Promise<UploadAttachmentsResult> {
  let currentCtx = { ...ctx }
  const ids: IRelationshipItem[] = []

  for (const file of files) {
    const item = mapFileToUploadItem(file)
    const responseData = await galleryApi.upload(
      url,
      item.file,
      (progress) => { item.progress = progress },
      currentCtx
    )
    const mapped = mapResponse(responseData)
    ids.push({
      id: mapped[0].id,
      type: mapped[0].type
    })
    currentCtx = mergeCorrelationFromResponse(currentCtx, responseData)
  }

  return { ids, context: currentCtx }
}

export async function uploadPostAttachments(
  files: File[],
  ctx: ApiRequestContext = {}
): Promise<UploadAttachmentsResult> {
  const fileGroups = groupFileTypes(files)
  let currentCtx = { ...ctx }
  const allIds: IRelationshipItem[] = []

  if (fileGroups.images.length) {
    const result = await uploadFilesToUrl(
      `v1/gallery/albums/${DEFAULT_ALBUM_ID}/images/upload`,
      fileGroups.images,
      currentCtx
    )
    allIds.push(...result.ids)
    currentCtx = result.context
  }

  if (fileGroups.videos.length) {
    const result = await uploadFilesToUrl(
      `v1/gallery/albums/${DEFAULT_ALBUM_ID}/videos/upload`,
      fileGroups.videos,
      currentCtx
    )
    allIds.push(...result.ids)
    currentCtx = result.context
  }

  return { ids: allIds, context: currentCtx }
}

export function groupFileTypes(files: File[]): IFileGroups {
  const imagesMimePatterns = ['image/']
  const videoMimePatterns = ['video/']
  const audioMimePatterns = ['audio/']
  const documentMimePatterns = [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument'
  ]

  const result: IFileGroups = {
    images: [],
    videos: [],
    documents: [],
    music: [],
    other: []
  }

  files.forEach((file: File) => {
    const mime = file.type

    if (imagesMimePatterns.some(pattern => mime.startsWith(pattern))) {
      result.images.push(file)
    } else if (videoMimePatterns.some(pattern => mime.startsWith(pattern))) {
      result.videos.push(file)
    } else if (audioMimePatterns.some(pattern => mime.startsWith(pattern))) {
      result.music.push(file)
    } else if (documentMimePatterns.some(pattern => mime.includes(pattern))) {
      result.documents.push(file)
    } else {
      result.other.push(file)
    }
  })

  return result
}
