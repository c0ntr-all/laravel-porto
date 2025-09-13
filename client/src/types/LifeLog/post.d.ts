import { IUser } from 'src/types/user'

export interface IPost {
  id: string
  attributes: {
    title?: string
    content?: string
    datetime: string | null
    created_at: string | null
  }
  relationships: {
    user: {
      data: IUser,
    },
  }
}
