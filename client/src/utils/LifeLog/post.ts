import { IPost } from 'src/types'
import { isGalleryVideo } from 'src/utils/gallery'
import { isPostDocumentAttachment } from 'src/utils/document'

export function parsePostDate(post: IPost): Date {
  const time = post.time ?? '12:00:00'
  return new Date(`${post.date}T${time}`)
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
