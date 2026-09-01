export const LIFELOG_FILTER_QUERY_PREFIX = 'filter'

export const LIFELOG_FILTER_QUERY_KEYS = {
  tags: 'tags',
  tagsMode: 'tags_mode',
  search: 'search',
  dateFrom: 'date_from',
  dateTo: 'date_to',
  preset: 'preset'
} as const

export type LifeLogFilterQueryKey =
  typeof LIFELOG_FILTER_QUERY_KEYS[keyof typeof LIFELOG_FILTER_QUERY_KEYS]

export function buildLifeLogFilterQueryKey(key: LifeLogFilterQueryKey): string {
  return `${LIFELOG_FILTER_QUERY_PREFIX}[${key}]`
}
