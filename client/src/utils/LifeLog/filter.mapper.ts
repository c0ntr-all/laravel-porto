import { IFilter, ILifeLogFilter, IPost, IPreset } from 'src/types'
import { ITag } from 'src/types/tag'
import { PostContentTypeEnum } from 'src/enums/LifeLog/PostContentTypeEnum'
import { createEmptyLifeLogFilter } from 'src/utils/LifeLog/filter'
import { normalizePresetContentTypes } from 'src/api/mappers/LifeLog/preset.mapper'
import { parsePostDate, toDateOnly } from 'src/utils/LifeLog/post'

export function mapLifeLogFilterToApiFilter(filter: ILifeLogFilter): IFilter {
  const apiFilter: IFilter = {}

  if (filter.activePresetId) {
    apiFilter.preset = filter.activePresetId
  }

  if (filter.tags.length) {
    apiFilter.tags = filter.tags.map(tag => tag.id)
    apiFilter.tags_mode = filter.tags_mode
  }

  return apiFilter
}

export function resolvePresetTags(preset: IPreset, allTags: ITag[]): ITag[] {
  const names = preset.tags ?? preset.rules?.tags ?? []

  if (!names.length) {
    return []
  }

  return allTags.filter(tag => names.includes(tag.name))
}

export function mapPresetToLifeLogFilter(
  preset: IPreset,
  allTags: ITag[]
): ILifeLogFilter {
  const dateFrom = preset.date_from ?? preset.rules?.date_from ?? null
  const dateTo = preset.date_to ?? preset.rules?.date_to ?? null
  const ignoreTime = Boolean(
    preset.rules?.ignore_time ??
    (dateFrom && dateFrom.length <= 10 && dateTo && dateTo.length <= 10)
  )
  const contentTypes = normalizePresetContentTypes(
    preset.content_type ?? preset.rules?.content_type
  )

  return {
    ...createEmptyLifeLogFilter(),
    tags: resolvePresetTags(preset, allTags),
    tags_mode: 'and',
    date_from: dateFrom,
    date_to: dateTo,
    ignore_time: ignoreTime,
    content_types: contentTypes,
    activePresetId: preset.id
  }
}

function isPostWithinDateFilter(post: IPost, filter: ILifeLogFilter): boolean {
  if (!filter.date_from && !filter.date_to) {
    return true
  }

  if (filter.ignore_time) {
    const postDate = post.date

    if (filter.date_from && postDate < toDateOnly(filter.date_from)) {
      return false
    }

    if (filter.date_to && postDate > toDateOnly(filter.date_to)) {
      return false
    }

    return true
  }

  const postDate = parsePostDate(post)

  if (filter.date_from) {
    const from = new Date(filter.date_from.replace(' ', 'T'))
    if (postDate < from) {
      return false
    }
  }

  if (filter.date_to) {
    const to = new Date(filter.date_to.replace(' ', 'T'))
    if (postDate > to) {
      return false
    }
  }

  return true
}

function isPostMatchingTags(post: IPost, filter: ILifeLogFilter): boolean {
  if (!filter.tags.length) {
    return true
  }

  const postTagIds = new Set((post.tags ?? []).map(tag => tag.id))
  const requiredTagIds = filter.tags.map(tag => tag.id)

  if (filter.tags_mode === 'or') {
    return requiredTagIds.some(tagId => postTagIds.has(tagId))
  }

  return requiredTagIds.every(tagId => postTagIds.has(tagId))
}

function isPostMatchingContentTypes(post: IPost, filter: ILifeLogFilter): boolean {
  if (!filter.content_types.length) {
    return true
  }

  const contentType = post.content_type ?? PostContentTypeEnum.DEFAULT

  return filter.content_types.includes(contentType)
}

export function applyClientSidePostFilter(posts: IPost[], filter: ILifeLogFilter): IPost[] {
  const search = filter.search.trim().toLowerCase()

  return posts.filter(post => {
    if (!isPostMatchingContentTypes(post, filter)) {
      return false
    }

    if (!isPostMatchingTags(post, filter)) {
      return false
    }

    if (search) {
      const haystack = [
        post.title ?? '',
        post.content ?? '',
        ...(post.tags ?? []).map(tag => tag.name)
      ]
        .join(' ')
        .toLowerCase()

      if (!haystack.includes(search)) {
        return false
      }
    }

    return isPostWithinDateFilter(post, filter)
  })
}
