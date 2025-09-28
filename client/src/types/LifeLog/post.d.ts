import { IUser } from 'src/types/user'

export interface IPost {
  id: string
  title?: string
  content?: string
  datetime: string | null
  created_at: string | null
  user: IUser
}

interface ICreatePostPayload {
  title?: string
  content?: string
  datetime: string
}
