import { PostContentTypeEnum } from 'src/enums/LifeLog/PostContentTypeEnum'
import { ISeriesWatchProgress } from 'src/types/LifeLog/watch'

export interface IPostUpdateDto {
  title?: string
  content?: string
  content_type?: PostContentTypeEnum
  is_draft?: boolean
  date?: string
  time?: string | null
  tags?: string[]
  new_tags?: string[]
  deleted_attachments_ids?: string[]
  attachments?: {id: string, type: string}[]
  movie_id?: number
  movie_title?: string
  watch?: ISeriesWatchProgress | null
  started_at?: string | null
  task_id?: number
  album_id?: number
  note_id?: number
}
