import { nanoid } from 'nanoid'
import { IPost, IPostModel } from 'src/types'
import { IUser } from 'src/types/user'
import { INewTag, ITag } from 'src/types/tag'
import { isGalleryVideo } from 'src/utils/gallery'
import { isPostDocumentAttachment } from 'src/utils/document'

const OPTIMISTIC_POST_PREFIX = 'optimistic-post-'

export function isOptimisticPostId(id: string): boolean {
  return id.startsWith(OPTIMISTIC_POST_PREFIX)
}

export function parsePostDate(post: IPost): Date {
  const time = post.time ?? '12:00:00'
  return new Date(`${post.date}T${time}`)
}

export function insertPostByDate(posts: IPost[], post: IPost): void {
  const newTime = parsePostDate(post).getTime()
  const insertIndex = posts.findIndex(existing => parsePostDate(existing).getTime() < newTime)

  if (insertIndex === -1) {
    posts.push(post)
    return
  }

  posts.splice(insertIndex, 0, post)
}

export function replaceOptimisticPost(
  posts: IPost[],
  optimisticId: string,
  nextPost: IPost
): void {
  const index = posts.findIndex(post => post.id === optimisticId)

  if (index !== -1) {
    posts.splice(index, 1)
  }

  insertPostByDate(posts, nextPost)
}

function mapNewTagsToOptimisticTags(newTags: INewTag[]): ITag[] {
  return newTags.map(tag => ({
    id: `optimistic-tag-${tag.name}`,
    name: tag.name,
    slug: tag.name,
    content: '',
    created_at: '',
    updated_at: ''
  }))
}

export function buildOptimisticPost(model: IPostModel, user: IUser): IPost {
  const [datePart, timePart = ''] = model.datetime.split(' ')

  return {
    type: 'll_posts',
    id: `${OPTIMISTIC_POST_PREFIX}${nanoid()}`,
    title: model.title,
    content: model.content,
    date: datePart,
    time: model.isNullTime ? null : (timePart || null),
    created_at: null,
    user,
    tags: [...model.tags, ...mapNewTagsToOptimisticTags(model.newTags)],
    attachments: [],
    is_pending: true
  }
}

export function toDateOnly(value: string): string {
  return value.slice(0, 10)
}

export function formatPostDateTime(post: IPost): string {
  if (post.time) {
    return `${post.date} ${post.time}`
  }

  return post.date
}

export function countPostAttachments(post: IPost): {
  images: number
  videos: number
  documents: number
  total: number
} {
  const attachments = post.attachments ?? []
  let images = 0
  let videos = 0
  let documents = 0

  for (const attachment of attachments) {
    if (isPostDocumentAttachment(attachment)) {
      documents += 1
    } else if (isGalleryVideo(attachment)) {
      videos += 1
    } else {
      images += 1
    }
  }

  return {
    images,
    videos,
    documents,
    total: images + videos + documents
  }
}
