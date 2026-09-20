import { IUser } from 'src/types/user'
import { INewTag, ITag } from 'src/types/tag'
import { IPostDocumentAttachment } from 'src/types/document'
import { PostContentTypeEnum } from 'src/enums/LifeLog/PostContentTypeEnum'
import { IMovie } from 'src/types/Movie'

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

export type IPostAttachmentWithState = IPostAttachment & {
  is_deleted?: boolean
}

export interface IPost {
  type: string
  id: string
  title?: string
  content: string
  content_type: PostContentTypeEnum
  date: string
  time: string | null
  created_at: string | null
  user: IUser
  tags: ITag[]
  attachments: IPostAttachment[]
  movie?: IMovie | null
  is_pending?: boolean
}

export interface IPostModel {
  title?: string
  content: string
  content_type?: PostContentTypeEnum
  tags: ITag[]
  newTags: INewTag[]
  datetime: string
  isNullTime: boolean
  movie_id?: number | null
  movie_title?: string | null
}

export interface IPostUpdateModel extends IPostModel {
  attachments: IPostAttachmentWithState[]
}
