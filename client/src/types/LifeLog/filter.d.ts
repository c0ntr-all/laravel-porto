import { ITag } from 'src/types/tag'

export type LifeLogViewMode = 'expanded' | 'compact'

export interface ITagsFilterData {
  tags: ITag[]
  tags_mode: 'or' | 'and'
}

export interface ILifeLogFilter extends ITagsFilterData {
  search: string
  date_from: string | null
  date_to: string | null
  activePresetId: string | null
}
