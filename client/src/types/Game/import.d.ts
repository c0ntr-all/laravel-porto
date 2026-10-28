import { GameImportStatusEnum } from 'src/enums/Game/GameImportStatusEnum'
import { IGame } from './game'

export interface IGameImport {
  id: string
  steam_id: number
  game_id: number | null
  source_url: string
  status: GameImportStatusEnum
  http_status: number | null
  was_created: boolean | null
  parsed_payload: Record<string, unknown> | null
  meta: Record<string, unknown> | null
  error_message: string | null
  started_at: string | null
  finished_at: string | null
  duration_ms: number | null
  created_at: string | null
  game: IGame | null
}

export interface IGameImportListQuery {
  steam_id?: number
  status?: GameImportStatusEnum
  cursor?: string | null
}
