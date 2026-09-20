import { ILifeLogFilter } from 'src/types/LifeLog/filter'

export function createEmptyLifeLogFilter(): ILifeLogFilter {
  return {
    tags: [],
    tags_mode: 'and',
    search: '',
    date_from: null,
    date_to: null,
    ignore_time: false,
    content_types: [],
    activePresetId: null
  }
}

export function cloneLifeLogFilter(filter: ILifeLogFilter): ILifeLogFilter {
  return {
    ...filter,
    tags: [...filter.tags],
    content_types: [...filter.content_types]
  }
}
