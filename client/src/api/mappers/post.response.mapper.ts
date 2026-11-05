import { IPost, IPostAlbumSubject, IPostTaskSubject } from 'src/types'
import { mapPostAttachmentsResponse } from 'src/api/mappers/attachment.mapper'
import { normalizeMovie } from 'src/api/mappers/Movie/movie.mapper'
import { PostContentTypeEnum } from 'src/enums/LifeLog/PostContentTypeEnum'
import { IMovie } from 'src/types/Movie'
import { normalizeSeriesWatchProgress } from 'src/utils/LifeLog/seriesWatch'
import { normalizeStartedAt } from 'src/utils/LifeLog/startedAt'

type PostWithRelations = IPost & {
  movies?: Record<string, unknown>[]
  movie?: Record<string, unknown> | null
  tasks?: Record<string, unknown>[]
  task?: Record<string, unknown> | null
  albums?: Record<string, unknown>[]
  album?: Record<string, unknown> | null
  watch?: unknown
  started_at?: unknown
  attributes?: Record<string, unknown>
}

function asRecord (value: unknown): Record<string, unknown> | null {
  if (!value || typeof value !== 'object') {
    return null
  }

  return value as Record<string, unknown>
}

function extractPostMovie (post: PostWithRelations): IMovie | null {
  const raw = post.movie ?? post.movies?.[0]

  if (!raw || typeof raw !== 'object') {
    return null
  }

  return normalizeMovie(raw)
}

function extractPostTask (post: PostWithRelations): IPostTaskSubject | null {
  const raw = asRecord(post.task) ?? asRecord(post.tasks?.[0])
  if (!raw) {
    return null
  }

  const taskList = asRecord(raw.taskList) ?? asRecord(raw.task_list)
  const listTitle = taskList?.title == null ? null : String(taskList.title)

  return {
    id: String(raw.id),
    title: String(raw.title ?? ''),
    task_list_id: raw.task_list_id == null ? null : String(raw.task_list_id),
    task_list_title: listTitle
  }
}

function extractPostAlbum (post: PostWithRelations): IPostAlbumSubject | null {
  const raw = asRecord(post.album) ?? asRecord(post.albums?.[0])
  if (!raw) {
    return null
  }

  const imagesCount = raw.images_count == null ? null : Number(raw.images_count)
  const videosCount = raw.videos_count == null ? null : Number(raw.videos_count)
  let mediaCount: number | undefined

  if (raw.media_count != null) {
    mediaCount = Number(raw.media_count)
  } else if (imagesCount != null || videosCount != null) {
    mediaCount = (imagesCount ?? 0) + (videosCount ?? 0)
  }

  return {
    id: String(raw.id),
    name: String(raw.name ?? ''),
    description: raw.description == null ? null : String(raw.description),
    image: raw.image == null || raw.image === '' ? null : String(raw.image),
    media_count: mediaCount
  }
}

export function normalizePost (post: IPost): IPost {
  const withRelations = post as PostWithRelations
  const rawWatch = withRelations.watch ?? withRelations.attributes?.watch
  const rawStartedAt = withRelations.started_at ?? withRelations.attributes?.started_at

  return {
    ...post,
    content_type: post.content_type ?? PostContentTypeEnum.DEFAULT,
    is_draft: Boolean(post.is_draft),
    attachments: mapPostAttachmentsResponse(post.attachments ?? []),
    movie: extractPostMovie(withRelations),
    task: extractPostTask(withRelations),
    album: extractPostAlbum(withRelations),
    watch: normalizeSeriesWatchProgress(rawWatch),
    started_at: normalizeStartedAt(rawStartedAt),
    comments_count: normalizeCommentsCount(post.comments_count)
  }
}

function normalizeCommentsCount (value: unknown): number {
  const count = Number(value ?? 0)

  if (!Number.isFinite(count) || count < 0) {
    return 0
  }

  return Math.trunc(count)
}

export function normalizePosts (posts: IPost[]): IPost[] {
  return posts.map(normalizePost)
}
