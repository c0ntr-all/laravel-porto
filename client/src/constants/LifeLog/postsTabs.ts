import {
  POST_CONTENT_TYPE_LABELS,
  POST_CONTENT_TYPES,
  PostContentTypeEnum
} from 'src/enums/LifeLog/PostContentTypeEnum'

export const LIFELOG_POSTS_TAB_ALL = 'all' as const
export const LIFELOG_POSTS_TAB_DRAFTS = 'drafts' as const

export type LifeLogPostsTabId =
  | typeof LIFELOG_POSTS_TAB_ALL
  | typeof LIFELOG_POSTS_TAB_DRAFTS
  | PostContentTypeEnum

export interface ILifeLogPostsTab {
  id: LifeLogPostsTabId
  label: string
}

export function buildLifeLogPostsTabs (): ILifeLogPostsTab[] {
  return [
    { id: LIFELOG_POSTS_TAB_ALL, label: 'Все посты' },
    ...POST_CONTENT_TYPES.map(type => ({
      id: type,
      label: POST_CONTENT_TYPE_LABELS[type]
    })),
    { id: LIFELOG_POSTS_TAB_DRAFTS, label: 'Черновики' }
  ]
}

export function isLifeLogPostsTabId (value: unknown): value is LifeLogPostsTabId {
  if (value === LIFELOG_POSTS_TAB_ALL || value === LIFELOG_POSTS_TAB_DRAFTS) {
    return true
  }

  return POST_CONTENT_TYPES.includes(value as PostContentTypeEnum)
}
