import { IPost, IPostModel, IPostUpdateModel, IPostAttachmentWithState } from 'src/types'
import { INewTag, ITag } from 'src/types/tag'
import { IPostCreateDto } from 'src/api/DTO/PostCreateDto'
import { IPostUpdateDto } from 'src/api/DTO/PostUpdateDto'
import { getPostAttachmentDeleteId } from 'src/utils/attachment'
import { PostContentTypeEnum } from 'src/enums/LifeLog/PostContentTypeEnum'

export function mapPostFormToCreateDto(postModel: IPostModel): IPostCreateDto {
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

  return data
}

export function mapPostFormToUpdateDto(edited: IPostUpdateModel, original: IPost): IPostUpdateDto {
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

  return dto
}
