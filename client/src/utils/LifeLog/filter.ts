import { ILifeLogFilter } from 'src/types/LifeLog/filter'

export function createEmptyLifeLogFilter(): ILifeLogFilter {
  return {
    tags: [],
    tags_mode: 'and',
    search: '',
    date_from: null,
    date_to: null,
    activePresetId: null
  }
}

export function cloneLifeLogFilter(filter: ILifeLogFilter): ILifeLogFilter {
  return {
    ...filter,
    tags: [...filter.tags]
  }
}
