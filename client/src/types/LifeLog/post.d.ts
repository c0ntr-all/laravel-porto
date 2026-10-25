import { IUser } from 'src/types/user'
import { INewTag, ITag } from 'src/types/tag'
import { IPostDocumentAttachment } from 'src/types/document'
import { PostContentTypeEnum } from 'src/enums/LifeLog/PostContentTypeEnum'
import { IMovie } from 'src/types/Movie'
import { ISeriesWatchProgress } from 'src/types/LifeLog/watch'
import { IGalleryAlbum, IGalleryMediaItem } from 'src/types/gallery'

export interface IPostTaskSubject {
  id: string
  title: string
  task_list_id: string | null
  task_list_title: string | null
}

export interface IPostAlbumSubject {
  id: string
  name: string
  description: string | null
  image: string | null
  media_count?: number
}

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
  is_draft: boolean
  date: string
  time: string | null
  created_at: string | null
  user: IUser
  tags: ITag[]
  attachments: IPostAttachment[]
  movie?: IMovie | null
  task?: IPostTaskSubject | null
  album?: IPostAlbumSubject | null
  watch?: ISeriesWatchProgress | null
  started_at?: string | null
  is_pending?: boolean
}

export interface IPostModel {
  title?: string
  content: string
  content_type?: PostContentTypeEnum
  is_draft: boolean
  tags: ITag[]
  newTags: INewTag[]
  datetime: string
  isNullTime: boolean
  movie_id?: number | null
  movie_title?: string | null
  watch?: ISeriesWatchProgress | null
  started_at?: string | null
  task_id?: number | null
  album_id?: number | null
  task?: IPostTaskSubject | null
  album?: IPostAlbumSubject | IGalleryAlbum | null
}

export interface IPostUpdateModel extends IPostModel {
  attachments: IPostAttachmentWithState[]
}

export type IPostFormPendingAttachment =
  | {
      key: string
      source: 'device'
      file: File
    }
  | {
      key: string
      source: 'gallery'
      media: IGalleryMediaItem
    }
