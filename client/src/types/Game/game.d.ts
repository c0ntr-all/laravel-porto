export interface IGame {
  id: string
  name: string
  description: string | null
  img: string | null
  air_date: string | null
  developer: string | null
  publisher: string | null
  steam_id: number | null
  created_at: string | null
  updated_at: string | null
}

export interface IGameWriteDto {
  name: string
  description: string | null
  img: string | null
  air_date: string | null
  developer: string | null
  publisher: string | null
  steam_id: number | null
}

export interface IGameListQuery {
  name?: string
}
