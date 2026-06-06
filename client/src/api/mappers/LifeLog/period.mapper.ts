import { IPeriodModel } from 'src/types'
import { IPeriodCreateDto } from 'src/api/DTO/PeriodCreateDto'

export function mapPeriodFormModelToCreateDto(periodModel: IPeriodModel): IPeriodCreateDto {
  const data: IPeriodCreateDto = {
    title: periodModel.title,
    start_post_id: periodModel.start_post_id,
    end_post_id: periodModel.end_post_id,
    color: periodModel.color
  }

  if (periodModel.description) {
    data.description = periodModel.description
  }
  if (periodModel.icon) {
    data.icon = periodModel.icon
  }

  return data
}
