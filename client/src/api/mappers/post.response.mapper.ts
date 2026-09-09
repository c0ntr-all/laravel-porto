import { IPost } from 'src/types'
import { mapPostAttachmentsResponse } from 'src/api/mappers/attachment.mapper'
import { PostContentTypeEnum } from 'src/enums/LifeLog/PostContentTypeEnum'

export function normalizePost(post: IPost): IPost {
  return {
    ...post,
    content_type: post.content_type ?? PostContentTypeEnum.DEFAULT,
    attachments: mapPostAttachmentsResponse(post.attachments ?? [])
  }
}

export function normalizePosts(posts: IPost[]): IPost[] {
  return posts.map(normalizePost)
}
