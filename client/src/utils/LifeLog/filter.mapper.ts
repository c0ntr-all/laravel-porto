import { IFilter, ILifeLogFilter, IPost, IPreset } from 'src/types'
import { ITag } from 'src/types/tag'
import { createEmptyLifeLogFilter } from 'src/utils/LifeLog/filter'
import { parsePostDate } from 'src/utils/LifeLog/post'

export function mapLifeLogFilterToApiFilter(filter: ILifeLogFilter): IFilter {
  const apiFilter: IFilter = {}

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
  return {
    ...createEmptyLifeLogFilter(),
    tags: resolvePresetTags(preset, allTags),
    tags_mode: 'and',
    date_from: preset.date_from ?? preset.rules?.date_from ?? null,
    date_to: preset.date_to ?? preset.rules?.date_to ?? null,
    activePresetId: preset.id
  }
}

export function applyClientSidePostFilter(posts: IPost[], filter: ILifeLogFilter): IPost[] {
  const search = filter.search.trim().toLowerCase()

  return posts.filter(post => {
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

    if (filter.date_from || filter.date_to) {
      const postDate = parsePostDate(post)

      if (filter.date_from) {
        const from = new Date(filter.date_from)
        if (postDate < from) {
          return false
        }
      }

      if (filter.date_to) {
        const to = new Date(filter.date_to)
        if (postDate > to) {
          return false
        }
      }
    }

    return true
  })
}
