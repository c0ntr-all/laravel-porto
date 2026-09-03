import { IJsonApiResponse } from 'src/types'
import { IPost, IPreset } from 'src/types'
import { IPresetModel, IPresetRules } from 'src/types/LifeLog/preset'
import { IPresetCreateDto } from 'src/api/DTO/PresetCreateDto'
import { mapResponse } from 'src/utils/jsonApiMapper'

export function formatPostDatetime(post: IPost): string {
  if (post.time) {
    return `${post.date} ${post.time}`
  }

  return `${post.date} 00:00`
}

function normalizePresetTags(tags: unknown): string[] {
  if (!Array.isArray(tags)) return []

  return tags
    .map(tag => (typeof tag === 'string' ? tag : tag?.name))
    .filter((tag): tag is string => Boolean(tag))
}

export function normalizePreset(raw: Record<string, unknown>): IPreset {
  const rules = raw.rules as IPresetRules | undefined

  return {
    ...(raw as unknown as IPreset),
    date_from: rules?.date_from ?? (raw.date_from as string | null) ?? (raw.start_date as string | null) ?? null,
    date_to: rules?.date_to ?? (raw.date_to as string | null) ?? (raw.end_date as string | null) ?? null,
    tags: normalizePresetTags(rules?.tags ?? raw.tags),
    rules
  }
}

export function mapPresetsResponse(response: IJsonApiResponse): IPreset[] {
  return mapResponse(response).map(item => normalizePreset(item))
}

export function mapPresetResponse(response: IJsonApiResponse): IPreset {
  return normalizePreset(mapResponse(response)[0])
}

export function mapPresetToFormModel(preset: IPreset): IPresetModel {
  return {
    title: preset.title ?? '',
    color: preset.color,
    date_from: preset.date_from,
    date_to: preset.date_to,
    tags: normalizePresetTags(preset.tags)
  }
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
