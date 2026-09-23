import { PostContentTypeEnum } from 'src/enums/LifeLog/PostContentTypeEnum'
import { ISeriesWatchProgress } from 'src/types/LifeLog/watch'

export interface IPostCreateDto {
  title?: string
  content?: string
  content_type?: PostContentTypeEnum
  date: string
  time: string | null
  tags?: string[]
  new_tags?: string[]
  attachments?: {id: string, type: string}[]
  movie_id?: number
  movie_title?: string
  watch?: ISeriesWatchProgress
}
