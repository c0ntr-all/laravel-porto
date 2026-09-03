import { attachmentApi } from 'src/api/requests/attachmentApi'
import { mapAttachmentsUploadResponse } from 'src/api/mappers/attachment.mapper'
import { mapFileToUploadItem } from 'src/api/mappers/gallery.mapper'
import { galleryApi } from 'src/api/requests/galleryApi'
import { LL_POST_ATTACHABLE_TYPE } from 'src/constants/LifeLog/attachment'
import { mapResponse } from 'src/utils/jsonApiMapper'
import { mergeCorrelationFromResponse } from 'src/utils/correlation'
import { isDocumentFile } from 'src/utils/document'
import { ApiRequestContext, IPostAttachment, IRelationshipItem } from 'src/types'

const DEFAULT_ALBUM_ID = 1

interface IFileGroups {
  images: File[]
  videos: File[]
  documents: File[]
  other: File[]
}

export interface UploadAttachmentsResult {
  ids: IRelationshipItem[]
  context: ApiRequestContext
}

export interface UploadDocumentsResult {
  attachments: IPostAttachment[]
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
      progress => { item.progress = progress },
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

export async function uploadPostGalleryAttachments(
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

export async function uploadPostDocuments(
  postId: string,
  files: File[],
  ctx: ApiRequestContext = {}
): Promise<UploadDocumentsResult> {
  if (!files.length) {
    return { attachments: [], context: ctx }
  }

  const responseData = await attachmentApi.upload(
    LL_POST_ATTACHABLE_TYPE,
    postId,
    files,
    ctx
  )

  return {
    attachments: mapAttachmentsUploadResponse(responseData),
    context: mergeCorrelationFromResponse(ctx, responseData)
  }
}

export async function uploadPostAttachments(
  files: File[],
  ctx: ApiRequestContext = {}
): Promise<UploadAttachmentsResult> {
  return uploadPostGalleryAttachments(files, ctx)
}

export function groupFileTypes(files: File[]): IFileGroups {
  const result: IFileGroups = {
    images: [],
    videos: [],
    documents: [],
    other: []
  }

  files.forEach(file => {
    const mime = file.type.toLowerCase()

    if (mime.startsWith('image/')) {
      result.images.push(file)
      return
    }

    if (mime.startsWith('video/')) {
      result.videos.push(file)
      return
    }

    if (isDocumentFile(file)) {
      result.documents.push(file)
      return
    }

    result.other.push(file)
  })

  return result
}

export function getUnsupportedFiles(files: File[]): File[] {
  return groupFileTypes(files).other
}
