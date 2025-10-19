import { IUser } from 'src/types/user'
import { INewTag, ITag } from 'src/types/tag'
import { IAttachment } from 'src/types/attachment'

export interface IPost {
  id: string
  title?: string
  content?: string
  datetime: string | null
  created_at: string | null
  user: IUser
  tags: ITag[]
  attachments?: IAttachment[]
}

export interface IPostModel {
  title: string,
  content: string,
  tags: ITag[],
  newTags: INewTag[],
  datetime: string | null
}

export interface IPostFilter {
  filter?: {
    [key: string]: string | number
  }
}
