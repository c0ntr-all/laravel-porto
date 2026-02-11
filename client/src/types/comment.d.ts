import { IIncludedItem } from 'src/components/types'
import { IUser } from 'src/types/user'

export interface ICommentPayload {
  commentable_id: string,
  commentable_type: string,
  content: string
}

export interface ICreateCommentResponse {
  data: {
    type: string
    id: string
    attributes: {
      name: string
      content: string
      created_at: string
    }
    relationships: {
      user: {
        data: IUser
      }
    }
  },
  included: IIncludedItem[]
  meta: {
    message?: string
  }
}
