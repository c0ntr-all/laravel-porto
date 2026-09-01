import { IPostAttachment } from 'src/types'
import { isGalleryVideo } from 'src/utils/gallery'
import { isPostDocumentAttachment } from 'src/utils/document'

export function isPostMediaAttachment(
  attachment: IPostAttachment
): boolean {
  return !isPostDocumentAttachment(attachment)
}

export function splitPostAttachments(attachments: IPostAttachment[] = []): {
  media: IPostAttachment[]
  documents: IPostAttachment[]
} {
  const media: IPostAttachment[] = []
  const documents: IPostAttachment[] = []

  for (const attachment of attachments) {
    if (isPostDocumentAttachment(attachment)) {
      documents.push(attachment)
      continue
    }

    media.push(attachment)
  }

  return { media, documents }
}

export function getPostAttachmentDeleteId(attachment: IPostAttachment): string {
  return attachment.attachment_id || attachment.id
}

export function isGalleryVideoAttachment(
  attachment: IPostAttachment
): boolean {
  return isPostMediaAttachment(attachment) && isGalleryVideo(attachment)
}
