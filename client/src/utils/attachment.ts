import {
  IGalleryMediaItem,
  IPostAttachment,
  IPostDocumentAttachment,
  IPostFormPendingAttachment,
  IPostGalleryAttachment,
  IRelationshipItem
} from 'src/types'
import { isGalleryVideo } from 'src/utils/gallery'
import { isPostDocumentAttachment } from 'src/utils/document'

export function isPostMediaAttachment(
  attachment: IPostAttachment
): attachment is IPostGalleryAttachment {
  return !isPostDocumentAttachment(attachment)
}

export function splitPostAttachments(attachments: IPostAttachment[] = []): {
  media: IPostGalleryAttachment[]
  documents: IPostDocumentAttachment[]
} {
  const media: IPostGalleryAttachment[] = []
  const documents: IPostDocumentAttachment[] = []

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
): attachment is IPostGalleryAttachment {
  return isPostMediaAttachment(attachment) && isGalleryVideo(attachment)
}

export function galleryMediaToRelationship(
  item: Pick<IGalleryMediaItem, 'id' | 'type' | 'attachment_type'>
): IRelationshipItem {
  return {
    id: item.id,
    type: item.attachment_type || (item.type === 'video' ? 'gallery_videos' : 'gallery_images')
  }
}

export function splitPendingAttachments(items: IPostFormPendingAttachment[] = []): {
  files: File[]
  galleryRefs: IRelationshipItem[]
} {
  const files: File[] = []
  const galleryRefs: IRelationshipItem[] = []

  for (const item of items) {
    if (item.source === 'device') {
      files.push(item.file)
      continue
    }

    galleryRefs.push(galleryMediaToRelationship(item.media))
  }

  return { files, galleryRefs }
}
