import { postApi } from 'src/api/requests/postApi'
import { mapPostFormToCreateDto, mapPostFormToUpdateDto } from 'src/api/mappers/post.mapper'
import { mapResponse } from 'src/utils/jsonApiMapper'
import { uploadPostAttachments } from 'src/services/post.service'
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

export async function createPostWithAttachments(
  postModel: IPostModel,
  files: File[],
  initialCtx: ApiRequestContext = {}
): Promise<PostOperationResult> {
  let ctx = { ...initialCtx }
  const dto = mapPostFormToCreateDto(postModel)

  if (files?.length) {
    const uploadResult = await uploadPostAttachments(files, ctx)
    dto.attachments = uploadResult.ids
    ctx = uploadResult.context
  }

  const response = await postApi.createPost(dto, ctx)
  const post = mapResponse(response)[0] as IPost

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

  if (files?.length) {
    const uploadResult = await uploadPostAttachments(files, ctx)
    dto.attachments = uploadResult.ids
    ctx = uploadResult.context
  }

  const response = await postApi.updatePost(id, dto, ctx)
  const post = mapResponse(response)[0] as IPost

  return { post, response }
}
