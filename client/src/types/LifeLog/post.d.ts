import { IUser } from 'src/types/user'
import { INewTag, ITag } from 'src/types/tag'
import { IPostDocumentAttachment } from 'src/types/document'

export interface IPostGalleryAttachment {
  id: string
  type: string
  attachment_type: 'gallery_images' | 'gallery_videos' | string
  attachment_id: string
  attachment_created_at?: string
  description: string | null
  source: string
  width: number
  height: number
  original_path: string
  list_thumb_path: string
  preview_thumb_path: string
  duration?: string
}

export type IPostAttachment = IPostGalleryAttachment | IPostDocumentAttachment

export interface IPostAttachmentWithState extends IPostAttachment {
  is_deleted?: boolean
}

export interface IPost {
  type: string
  id: string
  title?: string
  content: string
  date: string
  time: string | null
  created_at: string | null
  user: IUser
  tags: ITag[]
  attachments: IPostAttachment[]
}

export interface IPostModel {
  title?: string
  content: string
  tags: ITag[]
  newTags: INewTag[]
  datetime: string
  isNullTime: boolean
}

export interface IPostUpdateModel extends IPostModel {
  attachments: IPostAttachmentWithState[]
}
