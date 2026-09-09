import { PostContentTypeEnum } from 'src/enums/LifeLog/PostContentTypeEnum'

export interface IPostCreateDto {
  title?: string
  content?: string
  content_type?: PostContentTypeEnum
  date: string
  time: string | null
  tags?: string[]
  new_tags?: string[]
  attachments?: {id: string, type: string}[]
}
