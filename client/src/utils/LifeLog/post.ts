import { IPost } from 'src/types'
import { isGalleryVideo } from 'src/utils/gallery'

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
  total: number
} {
  const attachments = post.attachments ?? []
  let images = 0
  let videos = 0

  for (const attachment of attachments) {
    if (isGalleryVideo(attachment)) {
      videos += 1
    } else {
      images += 1
    }
  }

  return {
    images,
    videos,
    total: images + videos
  }
}
