import { IMusicTagGroup } from './tagGroup'

export interface IMusicTag {
  id: string
  name: string
  slug: string
  description: string | null
  is_active: boolean
  parent_id: string | null
  group_id: string | null
  group?: IMusicTagGroup | null
  tags: IMusicTag[]
  content?: string | null
  is_base?: boolean
}

export interface IMusicTagWriteDto {
  name: string
  slug?: string | null
  description?: string | null
  group_id?: number | null
  parent_id?: number | null
  is_active?: boolean
}
