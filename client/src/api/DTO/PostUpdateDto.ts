import { PostContentTypeEnum } from 'src/enums/LifeLog/PostContentTypeEnum'

export interface IPostUpdateDto {
  title?: string
  content?: string
  content_type?: PostContentTypeEnum
  date?: string
  time?: string | null
  tags?: string[]
  new_tags?: string[]
  deleted_attachments_ids?: string[]
  attachments?: {id: string, type: string}[]
}
