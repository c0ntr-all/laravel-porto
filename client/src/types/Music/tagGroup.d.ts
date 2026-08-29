export interface IMusicTagGroup {
  id: string
  name: string
  slug: string
  description: string | null
  is_system: boolean
  is_active: boolean
  display_order: number
}

export interface IMusicTagGroupWriteDto {
  name: string
  slug?: string | null
  description?: string | null
  is_system?: boolean
  is_active?: boolean
  display_order?: number
}
