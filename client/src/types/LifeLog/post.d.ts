import { IUser } from 'src/types/user'
import { INewTag, ITag } from 'src/types/tag'
import { IGalleryImage, IGalleryImageWithState } from 'src/types/gallery'

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
  attachments: IGalleryImage[] //TODO: Пока только Image. При добавлении других типов, это будет изменено
}

type IPostWithAttachmentWithState = IGalleryImageWithState & {
  attachments?: IGalleryImageWithState[]
}

export interface IPostModel {
  title?: string,
  content: string,
  tags: ITag[],
  newTags: INewTag[],
  datetime: string,
  isNullTime: boolean,
}

type IPostUpdateModel = IPostModel & {
  attachments: IPostWithAttachmentWithState[]
}

export interface IPostFilter {
  filter?: {
    [key: string]: string | number
  }
}

interface ITagsFilterData {
  tags: ITag[],
  tags_mode: 'or' | 'and'
}
