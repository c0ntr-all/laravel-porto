import { IUser } from 'src/types/user'
import { INewTag, ITag } from 'src/types/tag'
import { IAttachment, IAttachmentWithState } from 'src/types/attachment'

export interface IPost {
  type: string
  id: string
  title?: string
  content: string
  datetime: string | null
  created_at: string | null
  user: IUser
  tags: ITag[]
  attachments: IAttachment[]
}

type IPostWithAttachmentWithState = IAttachmentWithState & {
  attachments?: IAttachmentWithState[]
}

export interface IPostModel {
  title?: string,
  content: string,
  tags: ITag[],
  newTags: INewTag[],
  datetime: string | null
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
