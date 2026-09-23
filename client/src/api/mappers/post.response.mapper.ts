import { IPost } from 'src/types'
import { mapPostAttachmentsResponse } from 'src/api/mappers/attachment.mapper'
import { normalizeMovie } from 'src/api/mappers/Movie/movie.mapper'
import { PostContentTypeEnum } from 'src/enums/LifeLog/PostContentTypeEnum'
import { IMovie } from 'src/types/Movie'
import { normalizeSeriesWatchProgress } from 'src/utils/LifeLog/seriesWatch'

type PostWithMovieRelations = IPost & {
  movies?: Record<string, unknown>[]
  movie?: Record<string, unknown> | null
  watch?: unknown
  attributes?: Record<string, unknown>
}

function extractPostMovie (post: PostWithMovieRelations): IMovie | null {
  const raw = post.movie ?? post.movies?.[0]

  if (!raw || typeof raw !== 'object') {
    return null
  }

  return normalizeMovie(raw)
}

export function normalizePost (post: IPost): IPost {
  const withRelations = post as PostWithMovieRelations
  const rawWatch = withRelations.watch ?? withRelations.attributes?.watch

  return {
    ...post,
    content_type: post.content_type ?? PostContentTypeEnum.DEFAULT,
    attachments: mapPostAttachmentsResponse(post.attachments ?? []),
    movie: extractPostMovie(withRelations),
    watch: normalizeSeriesWatchProgress(rawWatch)
  }
}

export function normalizePosts (posts: IPost[]): IPost[] {
  return posts.map(normalizePost)
}
