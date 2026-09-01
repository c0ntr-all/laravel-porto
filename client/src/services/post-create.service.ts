import { postApi } from 'src/api/requests/postApi'
import { mapPostFormToCreateDto, mapPostFormToUpdateDto } from 'src/api/mappers/post.mapper'
import { mapResponse } from 'src/utils/jsonApiMapper'
import { mergeCorrelationFromResponse } from 'src/utils/correlation'
import {
  groupFileTypes,
  uploadPostDocuments,
  uploadPostGalleryAttachments
} from 'src/services/post.service'
import {
  ApiRequestContext,
  IJsonApiResponse,
  IPost,
  IPostModel,
  IPostUpdateModel
} from 'src/types'

interface PostOperationResult {
  post: IPost
  response: IJsonApiResponse
}

function mergePostAttachments(post: IPost, attachments: IPost['attachments']): IPost {
  return {
    ...post,
    attachments: [...(post.attachments ?? []), ...attachments]
  }
}

export async function createPostWithAttachments(
  postModel: IPostModel,
  files: File[],
  initialCtx: ApiRequestContext = {}
): Promise<PostOperationResult> {
  let ctx = { ...initialCtx }
  const dto = mapPostFormToCreateDto(postModel)
  const fileGroups = groupFileTypes(files)

  if (fileGroups.images.length || fileGroups.videos.length) {
    const uploadResult = await uploadPostGalleryAttachments(
      [...fileGroups.images, ...fileGroups.videos],
      ctx
    )
    dto.attachments = uploadResult.ids
    ctx = uploadResult.context
  }

  const response = await postApi.createPost(dto, ctx)
  let post = mapResponse(response)[0] as IPost
  ctx = mergeCorrelationFromResponse(ctx, response)

  if (fileGroups.documents.length) {
    const documentUpload = await uploadPostDocuments(post.id, fileGroups.documents, ctx)
    post = mergePostAttachments(post, documentUpload.attachments)
    ctx = documentUpload.context
  }

  return { post, response }
}

export async function updatePostWithAttachments(
  id: string,
  postModel: IPostUpdateModel,
  originalPost: IPost,
  files: File[],
  initialCtx: ApiRequestContext = {}
): Promise<PostOperationResult> {
  let ctx = { ...initialCtx }
  const dto = mapPostFormToUpdateDto(postModel, originalPost)
  const fileGroups = groupFileTypes(files)

  if (fileGroups.images.length || fileGroups.videos.length) {
    const uploadResult = await uploadPostGalleryAttachments(
      [...fileGroups.images, ...fileGroups.videos],
      ctx
    )
    dto.attachments = uploadResult.ids
    ctx = uploadResult.context
  }

  const response = await postApi.updatePost(id, dto, ctx)
  let post = mapResponse(response)[0] as IPost
  ctx = mergeCorrelationFromResponse(ctx, response)

  if (fileGroups.documents.length) {
    const documentUpload = await uploadPostDocuments(id, fileGroups.documents, ctx)
    post = mergePostAttachments(post, documentUpload.attachments)
  }

  return { post, response }
}
