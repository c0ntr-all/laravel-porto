import { IHistoryItem, IJsonApiResponse } from 'src/types'
import { mapResponse } from 'src/utils/jsonApiMapper'
import { asRecord, asRecords } from 'src/api/mappers/Music/helpers'
import { normalizeTrack } from 'src/api/mappers/Music/track.mapper'

export function normalizeHistoryItem(raw: Record<string, unknown>): IHistoryItem {
  const trackRaw = asRecord(raw.track) ?? asRecords(raw.track)[0] ?? null

  return {
    id: String(raw.id),
    track_id: String(raw.track_id ?? ''),
    created_at: raw.created_at ? String(raw.created_at) : null,
    track: trackRaw ? normalizeTrack(trackRaw) : null
  }
}

export function mapHistoryResponse(response: IJsonApiResponse): IHistoryItem[] {
  return mapResponse(response).map(normalizeHistoryItem)
}
