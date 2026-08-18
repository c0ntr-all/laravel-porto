import { IPresetModel } from 'src/types'
import { IPresetCreateDto } from 'src/api/DTO/PresetCreateDto'

export function mapPresetFormModelToCreateDto(presetModel: IPresetModel): IPresetCreateDto {
  const data: IPresetCreateDto = {
    title: presetModel.title,
    start_post_id: presetModel.start_post_id,
    end_post_id: presetModel.end_post_id,
    color: presetModel.color
  }

  if (presetModel.description) {
    data.description = presetModel.description
  }
  if (presetModel.icon) {
    data.icon = presetModel.icon
  }

  return data
}
