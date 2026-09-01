import { LocationQuery } from 'vue-router'
import { ILifeLogFilter, IPreset } from 'src/types'
import { ITag } from 'src/types/tag'
import { createEmptyLifeLogFilter } from 'src/utils/LifeLog/filter'
import { mapPresetToLifeLogFilter } from 'src/utils/LifeLog/filter.mapper'
import {
  buildLifeLogFilterQueryKey,
  LIFELOG_FILTER_QUERY_KEYS,
  LIFELOG_FILTER_QUERY_PREFIX,
  LifeLogFilterQueryKey
} from 'src/constants/LifeLog/filterQuery'

function getQueryValue(query: LocationQuery, key: LifeLogFilterQueryKey): string | null {
  const bracketKey = buildLifeLogFilterQueryKey(key)
  const value = query[bracketKey] ?? query[key]

  if (Array.isArray(value)) {
    return value[0] ?? null
  }

  return value ?? null
}

export function hasLifeLogFilterQuery(query: LocationQuery): boolean {
  return Object.keys(query).some(key =>
    key.startsWith(`${LIFELOG_FILTER_QUERY_PREFIX}[`) || key === LIFELOG_FILTER_QUERY_PREFIX
  )
}

function resolveTagsFromQuery(rawTags: string | null, allTags: ITag[]): ITag[] {
  if (!rawTags) {
    return []
  }

  const tagIds = rawTags
    .split(',')
    .map(id => id.trim())
    .filter(Boolean)

  if (!tagIds.length) {
    return []
  }

  const tagsById = new Map(allTags.map(tag => [tag.id, tag]))

  return tagIds
    .map(id => tagsById.get(id))
    .filter((tag): tag is ITag => Boolean(tag))
}

function parseTagsMode(value: string | null): 'or' | 'and' {
  return value === 'or' ? 'or' : 'and'
}

export function parseLifeLogFilterFromQuery(
  query: LocationQuery,
  allTags: ITag[],
  presets: IPreset[] = []
): ILifeLogFilter | null {
  if (!hasLifeLogFilterQuery(query)) {
    return null
  }

  const presetId = getQueryValue(query, LIFELOG_FILTER_QUERY_KEYS.preset)
  const preset = presetId
    ? presets.find(item => item.id === presetId) ?? null
    : null

  const filter = preset
    ? mapPresetToLifeLogFilter(preset, allTags)
    : createEmptyLifeLogFilter()

  const tagsFromQuery = resolveTagsFromQuery(
    getQueryValue(query, LIFELOG_FILTER_QUERY_KEYS.tags),
    allTags
  )
  if (tagsFromQuery.length) {
    filter.tags = tagsFromQuery
  }

  const tagsMode = getQueryValue(query, LIFELOG_FILTER_QUERY_KEYS.tagsMode)
  if (tagsMode) {
    filter.tags_mode = parseTagsMode(tagsMode)
  }

  const search = getQueryValue(query, LIFELOG_FILTER_QUERY_KEYS.search)
  if (search !== null) {
    filter.search = search
  }

  const dateFrom = getQueryValue(query, LIFELOG_FILTER_QUERY_KEYS.dateFrom)
  if (dateFrom !== null) {
    filter.date_from = dateFrom || null
  }

  const dateTo = getQueryValue(query, LIFELOG_FILTER_QUERY_KEYS.dateTo)
  if (dateTo !== null) {
    filter.date_to = dateTo || null
  }

  if (presetId) {
    filter.activePresetId = presetId
  }

  return filter
}

export function serializeLifeLogFilterToQuery(
  filter: ILifeLogFilter
): Record<string, string> {
  const query: Record<string, string> = {}

  if (filter.tags.length) {
    query[buildLifeLogFilterQueryKey(LIFELOG_FILTER_QUERY_KEYS.tags)] =
      filter.tags.map(tag => tag.id).join(',')
    query[buildLifeLogFilterQueryKey(LIFELOG_FILTER_QUERY_KEYS.tagsMode)] = filter.tags_mode
  }

  const search = filter.search.trim()
  if (search) {
    query[buildLifeLogFilterQueryKey(LIFELOG_FILTER_QUERY_KEYS.search)] = search
  }

  if (filter.date_from) {
    query[buildLifeLogFilterQueryKey(LIFELOG_FILTER_QUERY_KEYS.dateFrom)] = filter.date_from
  }

  if (filter.date_to) {
    query[buildLifeLogFilterQueryKey(LIFELOG_FILTER_QUERY_KEYS.dateTo)] = filter.date_to
  }

  if (filter.activePresetId) {
    query[buildLifeLogFilterQueryKey(LIFELOG_FILTER_QUERY_KEYS.preset)] = filter.activePresetId
  }

  return query
}

export function stripLifeLogFilterQuery(query: LocationQuery): LocationQuery {
  const nextQuery: LocationQuery = { ...query }

  for (const key of Object.keys(nextQuery)) {
    if (key.startsWith(`${LIFELOG_FILTER_QUERY_PREFIX}[`) || key === LIFELOG_FILTER_QUERY_PREFIX) {
      delete nextQuery[key]
    }
  }

  return nextQuery
}

function normalizeQuery(query: LocationQuery): string {
  const entries = Object.entries(query)
    .filter(([, value]) => value !== undefined && value !== null && value !== '')
    .map(([key, value]) => [key, Array.isArray(value) ? value.join(',') : String(value)] as const)
    .sort(([left], [right]) => left.localeCompare(right))

  return JSON.stringify(entries)
}

export function areLocationQueriesEqual(
  left: LocationQuery,
  right: LocationQuery
): boolean {
  return normalizeQuery(left) === normalizeQuery(right)
}

export function hasActiveLifeLogFilter(filter: ILifeLogFilter): boolean {
  return Boolean(
    filter.tags.length ||
    filter.search.trim() ||
    filter.date_from ||
    filter.date_to ||
    filter.activePresetId
  )
}

export function areLifeLogFiltersEqual(
  left: ILifeLogFilter,
  right: ILifeLogFilter
): boolean {
  const leftTagIds = left.tags.map(tag => tag.id).sort().join(',')
  const rightTagIds = right.tags.map(tag => tag.id).sort().join(',')

  return (
    leftTagIds === rightTagIds &&
    left.tags_mode === right.tags_mode &&
    left.search === right.search &&
    left.date_from === right.date_from &&
    left.date_to === right.date_to &&
    left.activePresetId === right.activePresetId
  )
}
