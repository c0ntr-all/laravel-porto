import { IUser } from 'src/types/user'
import { ITag } from 'src/types/tag'

export interface IPost {
  id: string
  title?: string
  content?: string
  datetime: string | null
  created_at: string | null
  user: IUser
  tags: ITag[]
}

export interface IPostModel {
  title: string,
  content: string,
  tags: ITag[],
  datetime: string
}
