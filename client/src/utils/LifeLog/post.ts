import { nanoid } from 'nanoid'
import { IPost, IPostModel } from 'src/types'
import { IUser } from 'src/types/user'
import { INewTag, ITag } from 'src/types/tag'
import { isGalleryVideo } from 'src/utils/gallery'
import { isPostDocumentAttachment } from 'src/utils/document'
import { PostContentTypeEnum } from 'src/enums/LifeLog/PostContentTypeEnum'
import { MovieTypeEnum } from 'src/enums/Movie/MovieTypeEnum'

const OPTIMISTIC_POST_PREFIX = 'optimistic-post-'

export function isOptimisticPostId(id: string): boolean {
  return id.startsWith(OPTIMISTIC_POST_PREFIX)
}

export function isMovieWatchPost(post: IPost): boolean {
  return post.content_type === PostContentTypeEnum.MOVIE ||
    post.content_type === PostContentTypeEnum.TV_SERIES
}

export const MOVIE_WATCH_POST_TITLE = 'Просмотр фильма'
export const TV_SERIES_WATCH_POST_TITLE = 'Просмотр сериала'

export function movieWatchPostTitle(post: IPost): string {
  return post.content_type === PostContentTypeEnum.TV_SERIES
    ? TV_SERIES_WATCH_POST_TITLE
    : MOVIE_WATCH_POST_TITLE
}

export function parsePostDate(post: IPost): Date {
  const time = post.time ?? '12:00:00'
  return new Date(`${post.date}T${time}`)
}

export function insertPostByDate(posts: IPost[], post: IPost): void {
  const newTime = parsePostDate(post).getTime()
  const insertIndex = posts.findIndex(existing => parsePostDate(existing).getTime() < newTime)

  if (insertIndex === -1) {
    posts.push(post)
    return
  }

  posts.splice(insertIndex, 0, post)
}

export function replaceOptimisticPost(
  posts: IPost[],
  optimisticId: string,
  nextPost: IPost
): void {
  const index = posts.findIndex(post => post.id === optimisticId)

  if (index !== -1) {
    posts.splice(index, 1)
  }

  insertPostByDate(posts, nextPost)
}

function mapNewTagsToOptimisticTags(newTags: INewTag[]): ITag[] {
  return newTags.map(tag => ({
    id: `optimistic-tag-${tag.name}`,
    name: tag.name,
    slug: tag.name,
    content: '',
    created_at: '',
    updated_at: ''
  }))
}

export function buildOptimisticPost(model: IPostModel, user: IUser): IPost {
  const [datePart, timePart = ''] = model.datetime.split(' ')
  const contentType = model.content_type ?? PostContentTypeEnum.DEFAULT
  const isMoviePost =
    contentType === PostContentTypeEnum.MOVIE ||
    contentType === PostContentTypeEnum.TV_SERIES
  const isSeriesPost = contentType === PostContentTypeEnum.TV_SERIES
  const movieTitle = model.title?.trim() ?? ''

  return {
    type: 'll_posts',
    id: `${OPTIMISTIC_POST_PREFIX}${nanoid()}`,
    title: isMoviePost
      ? (isSeriesPost ? TV_SERIES_WATCH_POST_TITLE : MOVIE_WATCH_POST_TITLE)
      : model.title,
    movie: isMoviePost && movieTitle
      ? {
          id: model.movie_id != null
            ? String(model.movie_id)
            : `${OPTIMISTIC_POST_PREFIX}movie`,
          kp_id: 0,
          title: movieTitle,
          description: null,
          short_description: null,
          year: 0,
          type: isSeriesPost ? MovieTypeEnum.TV_SERIES : MovieTypeEnum.MOVIE,
          cover: null,
          kp_rating: null,
          kp_img: null,
          kp_imported_at: null,
          created_at: null,
          updated_at: null,
          genres: [],
          countries: [],
          actors_count: 0,
          folder_slugs: [],
          folder_ids: [],
          credits: []
        }
      : null,
    watch: isSeriesPost ? (model.watch ?? null) : null,
    content: model.content,
    content_type: model.content_type ?? PostContentTypeEnum.DEFAULT,
    date: datePart,
    time: model.isNullTime ? null : (timePart || null),
    created_at: null,
    user,
    tags: [...model.tags, ...mapNewTagsToOptimisticTags(model.newTags)],
    attachments: [],
    is_pending: true
  }
}

export function toDateOnly(value: string): string {
  return value.slice(0, 10)
}

export function formatPostDateTime(post: IPost): string {
  if (post.time) {
    return `${post.date} ${post.time}`
  }

  return post.date
}

export function countPostAttachments(post: IPost): {
  images: number
  videos: number
  documents: number
  total: number
} {
  const attachments = post.attachments ?? []
  let images = 0
  let videos = 0
  let documents = 0

  for (const attachment of attachments) {
    if (isPostDocumentAttachment(attachment)) {
      documents += 1
    } else if (isGalleryVideo(attachment)) {
      videos += 1
    } else {
      images += 1
    }
  }

  return {
    images,
    videos,
    documents,
    total: images + videos + documents
  }
}
