import { IPost } from 'src/types'
import { IPresetModel } from 'src/types/LifeLog/preset'
import { IPresetCreateDto } from 'src/api/DTO/PresetCreateDto'

export function formatPostDatetime(post: IPost): string {
  if (post.time) {
    return `${post.date} ${post.time}`
  }

  return `${post.date} 00:00`
}

export function mapPresetFormModelToCreateDto(presetModel: IPresetModel): IPresetCreateDto {
  const dto: IPresetCreateDto = {
    title: presetModel.title,
    color: presetModel.color,
    date_from: presetModel.date_from?.trim() || null,
    date_to: presetModel.date_to?.trim() || null
  }

  if (presetModel.tags?.length) {
    dto.tags = presetModel.tags
  }

  return dto
}
