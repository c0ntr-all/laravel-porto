import { IPost } from 'src/types'
import { mapPostAttachmentsResponse } from 'src/api/mappers/attachment.mapper'

export function normalizePost(post: IPost): IPost {
  return {
    ...post,
    attachments: mapPostAttachmentsResponse((post.attachments ?? []) as Record<string, unknown>[])
  }
}

export function normalizePosts(posts: IPost[]): IPost[] {
  return posts.map(normalizePost)
}
