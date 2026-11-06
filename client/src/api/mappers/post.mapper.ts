import { IPost, IPostModel, IPostUpdateModel, IPostAttachmentWithState } from 'src/types'
import { INewTag, ITag } from 'src/types/tag'
import { IPostCreateDto } from 'src/api/DTO/PostCreateDto'
import { IPostUpdateDto } from 'src/api/DTO/PostUpdateDto'
import { getPostAttachmentDeleteId } from 'src/utils/attachment'
import { PostContentTypeEnum } from 'src/enums/LifeLog/PostContentTypeEnum'
import { serializeSeriesWatchProgress } from 'src/utils/LifeLog/seriesWatch'
import { normalizeStartedAt } from 'src/utils/LifeLog/startedAt'

function resolvePostFormTime (model: Pick<IPostModel, 'datetime' | 'isNullTime'>): string | null {
  if (model.isNullTime) {
    return null
  }

  const timePart = model.datetime.trim().split(/\s+/)[1]
  if (!timePart) {
    return null
  }

  const match = timePart.match(/^(\d{1,2}):(\d{2})/)
  if (!match) {
    return null
  }

  return `${match[1].padStart(2, '0')}:${match[2]}`
}

function resolvePostFormDate (datetime: string): string {
  return datetime.trim().split(/\s+/)[0] ?? ''
}

function normalizeApiTime (time: string | null | undefined): string | null {
  if (!time) {
    return null
  }

  const match = time.trim().match(/^(\d{1,2}):(\d{2})/)
  if (!match) {
    return null
  }

  return `${match[1].padStart(2, '0')}:${match[2]}`
}

export function mapPostFormToCreateDto (postModel: IPostModel): IPostCreateDto {
  const data: IPostCreateDto = {
    title: postModel.title,
    content: postModel.content,
    content_type: postModel.content_type ?? PostContentTypeEnum.DEFAULT,
    is_draft: Boolean(postModel.is_draft),
    tags: postModel.tags.map((t: ITag) => t.id),
    date: resolvePostFormDate(postModel.datetime),
    time: resolvePostFormTime(postModel)
  }
  if (postModel.newTags) {
    data.new_tags = postModel.newTags.map((t: INewTag) => t.name)
  }

  const contentType = data.content_type ?? PostContentTypeEnum.DEFAULT
  if (
    contentType === PostContentTypeEnum.MOVIE ||
    contentType === PostContentTypeEnum.TV_SERIES
  ) {
    if (postModel.movie_id != null) {
      data.movie_id = postModel.movie_id
    } else if (postModel.movie_title?.trim()) {
      data.movie_title = postModel.movie_title.trim()
    }

    if (typeof postModel.started_at === 'string' && postModel.started_at.trim()) {
      const normalized = normalizeStartedAt(postModel.started_at)
      if (normalized) {
        data.started_at = normalized
      }
    }
  }

  if (contentType === PostContentTypeEnum.TV_SERIES) {
    const watch = serializeSeriesWatchProgress(postModel.watch)
    if (watch) {
      data.watch = watch
    }
  }

  if (contentType === PostContentTypeEnum.TASK && postModel.task_id != null) {
    data.task_id = postModel.task_id
  }

  if (contentType === PostContentTypeEnum.ALBUM && postModel.album_id != null) {
    data.album_id = postModel.album_id
  }

  if (contentType === PostContentTypeEnum.NOTE && postModel.note_id != null) {
    data.note_id = postModel.note_id
  }

  return data
}

export function mapPostFormToUpdateDto (edited: IPostUpdateModel, original: IPost): IPostUpdateDto {
  const dto: IPostUpdateDto = {}

  if (edited.title !== original.title) dto.title = edited.title
  if (edited.content !== original.content) dto.content = edited.content
  if (
    edited.content_type &&
    edited.content_type !== (original.content_type ?? PostContentTypeEnum.DEFAULT)
  ) {
    dto.content_type = edited.content_type
  }
  if (Boolean(edited.is_draft) !== Boolean(original.is_draft)) {
    dto.is_draft = Boolean(edited.is_draft)
  }

  const editedDate = resolvePostFormDate(edited.datetime ?? '')
  const editedTime = resolvePostFormTime(edited)
  const originalTime = normalizeApiTime(original.time)

  if (editedDate !== original.date || editedTime !== originalTime) {
    dto.date = editedDate
    dto.time = editedTime
  }

  const existingAttachments: IPostAttachmentWithState[] = edited.attachments.filter(
    file => file.is_deleted === true
  )
  if (existingAttachments.length) {
    dto.deleted_attachments_ids = existingAttachments.map(file =>
      getPostAttachmentDeleteId(file)
    )
  }

  // Tags will be synched in back
  dto.tags = edited.tags.map(t => t.id)

  if (edited.newTags.length > 0) dto.new_tags = edited.newTags.map(t => t.name)

  const contentType = edited.content_type ?? original.content_type ?? PostContentTypeEnum.DEFAULT
  const isMovieContent =
    contentType === PostContentTypeEnum.MOVIE ||
    contentType === PostContentTypeEnum.TV_SERIES

  if (isMovieContent) {
    if (edited.movie_id != null && edited.movie_id !== Number(original.movie?.id)) {
      dto.movie_id = edited.movie_id
    } else if (
      edited.movie_title?.trim() &&
      edited.movie_title.trim() !== (original.movie?.title ?? original.title ?? '')
    ) {
      dto.movie_title = edited.movie_title.trim()
    }

    const nextStartedAt = typeof edited.started_at === 'string' && edited.started_at.trim()
      ? normalizeStartedAt(edited.started_at)
      : null
    const prevStartedAt = normalizeStartedAt(original.started_at)

    if (nextStartedAt !== prevStartedAt) {
      dto.started_at = nextStartedAt
    }

    if (contentType === PostContentTypeEnum.TV_SERIES) {
      const nextWatch = serializeSeriesWatchProgress(edited.watch)
      const prevWatch = serializeSeriesWatchProgress(original.watch)
      if (JSON.stringify(nextWatch ?? null) !== JSON.stringify(prevWatch ?? null)) {
        if (nextWatch) {
          dto.watch = nextWatch
          dto.content_type = PostContentTypeEnum.TV_SERIES
        }
      }
    }
  }

  if (contentType === PostContentTypeEnum.TASK) {
    const nextTaskId = edited.task_id ?? null
    const prevTaskId = original.task ? Number(original.task.id) : null
    if (nextTaskId !== prevTaskId && nextTaskId != null) {
      dto.task_id = nextTaskId
      dto.content_type = PostContentTypeEnum.TASK
    }
  }

  if (contentType === PostContentTypeEnum.ALBUM) {
    const nextAlbumId = edited.album_id ?? null
    const prevAlbumId = original.album ? Number(original.album.id) : null
    if (nextAlbumId !== prevAlbumId && nextAlbumId != null) {
      dto.album_id = nextAlbumId
      dto.content_type = PostContentTypeEnum.ALBUM
    }
  }

  if (contentType === PostContentTypeEnum.NOTE) {
    const nextNoteId = edited.note_id ?? null
    const prevNoteId = original.note ? Number(original.note.id) : null
    if (nextNoteId !== prevNoteId && nextNoteId != null) {
      dto.note_id = nextNoteId
      dto.content_type = PostContentTypeEnum.NOTE
    }
  }

  return dto
}
