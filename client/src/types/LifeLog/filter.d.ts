import { ITag } from 'src/types/tag'
import { PostContentTypeEnum } from 'src/enums/LifeLog/PostContentTypeEnum'

export type LifeLogViewMode = 'expanded' | 'compact'

export interface ITagsFilterData {
  tags: ITag[]
  tags_mode: 'or' | 'and'
}

export interface ILifeLogFilter extends ITagsFilterData {
  search: string
  date_from: string | null
  date_to: string | null
  ignore_time: boolean
  content_types: PostContentTypeEnum[]
  activePresetId: string | null
}
