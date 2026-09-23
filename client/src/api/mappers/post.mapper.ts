import { IPost, IPostModel, IPostUpdateModel, IPostAttachmentWithState } from 'src/types'
import { INewTag, ITag } from 'src/types/tag'
import { IPostCreateDto } from 'src/api/DTO/PostCreateDto'
import { IPostUpdateDto } from 'src/api/DTO/PostUpdateDto'
import { getPostAttachmentDeleteId } from 'src/utils/attachment'
import { PostContentTypeEnum } from 'src/enums/LifeLog/PostContentTypeEnum'
import { serializeSeriesWatchProgress } from 'src/utils/LifeLog/seriesWatch'

export function mapPostFormToCreateDto (postModel: IPostModel): IPostCreateDto {
  const data: IPostCreateDto = {
    title: postModel.title,
    content: postModel.content,
    content_type: postModel.content_type ?? PostContentTypeEnum.DEFAULT,
    tags: postModel.tags.map((t: ITag) => t.id),
    date: postModel.datetime.split(' ')[0],
    time: postModel.isNullTime ? null : postModel.datetime.split(' ')[1]
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
  }

  if (contentType === PostContentTypeEnum.TV_SERIES) {
    const watch = serializeSeriesWatchProgress(postModel.watch)
    if (watch) {
      data.watch = watch
    }
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

  // Reconstructing datetime for compare
  let originalDateTime = original.date
  if (original.time) {
    originalDateTime += ' ' + original.time
  }
  if (edited.datetime !== originalDateTime) {
    dto.date = edited.datetime?.split(' ')[0]
    dto.time = edited.isNullTime ? null : edited.datetime?.split(' ')[1]
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

    if (contentType === PostContentTypeEnum.TV_SERIES) {
      const nextWatch = serializeSeriesWatchProgress(edited.watch)
      const prevWatch = serializeSeriesWatchProgress(original.watch)
      if (JSON.stringify(nextWatch ?? null) !== JSON.stringify(prevWatch ?? null)) {
        if (nextWatch) {
          dto.watch = nextWatch
        }
      }
    }
  }

  return dto
}
