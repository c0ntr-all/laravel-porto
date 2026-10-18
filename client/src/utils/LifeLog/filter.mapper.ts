import { IFilter, ILifeLogFilter, IPost, IPreset } from 'src/types'
import { ITag } from 'src/types/tag'
import { PostContentTypeEnum } from 'src/enums/LifeLog/PostContentTypeEnum'
import {
  LIFELOG_POSTS_TAB_ALL,
  LIFELOG_POSTS_TAB_DRAFTS,
  LifeLogPostsTabId
} from 'src/constants/LifeLog/postsTabs'
import { createEmptyLifeLogFilter } from 'src/utils/LifeLog/filter'
import { normalizePresetContentTypes } from 'src/api/mappers/LifeLog/preset.mapper'
import { parsePostDate, toDateOnly } from 'src/utils/LifeLog/post'

export function mapPostsTabToApiFilter (tab: LifeLogPostsTabId): IFilter {
  if (tab === LIFELOG_POSTS_TAB_DRAFTS) {
    return { is_draft: 1 }
  }

  if (tab === LIFELOG_POSTS_TAB_ALL) {
    return { is_draft: 0 }
  }

  return {
    is_draft: 0,
    content_type: tab
  }
}

export function mapLifeLogFilterToApiFilter (
  filter: ILifeLogFilter,
  postsTab: LifeLogPostsTabId = LIFELOG_POSTS_TAB_ALL
): IFilter {
  const apiFilter: IFilter = {
    ...mapPostsTabToApiFilter(postsTab)
  }

  if (filter.activePresetId) {
    apiFilter.preset = filter.activePresetId
  }

  if (filter.tags.length) {
    apiFilter.tags = filter.tags.map(tag => tag.id)
    apiFilter.tags_mode = filter.tags_mode
  }

  const search = filter.search.trim()
  if (search) {
    apiFilter.text = search
  }

  if (filter.date_from) {
    apiFilter.date_from = filter.date_from
  }

  if (filter.date_to) {
    apiFilter.date_to = filter.date_to
  }

  return apiFilter
}

export function postMatchesApiListFilter (post: IPost, filters: IFilter): boolean {
  if (filters.is_draft !== undefined && filters.is_draft !== null) {
    const wantDraft = filters.is_draft === 1 ||
      filters.is_draft === true ||
      filters.is_draft === '1' ||
      filters.is_draft === 'true'

    if (Boolean(post.is_draft) !== wantDraft) {
      return false
    }
  }

  if (filters.content_type !== undefined && filters.content_type !== null && filters.content_type !== '') {
    const raw = Array.isArray(filters.content_type)
      ? filters.content_type.map(String)
      : String(filters.content_type).split(',')
    const allowed = raw.map(item => item.trim()).filter(Boolean)
    const contentType = post.content_type ?? PostContentTypeEnum.DEFAULT

    if (allowed.length && !allowed.includes(contentType)) {
      return false
    }
  }

  return true
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
