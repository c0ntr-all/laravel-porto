export interface IPresetCreateDto {
  title: string
  description?: string | null
  date_from: string | null
  date_to?: string | null
  color: string
  icon?: string | null
  tags?: string[]
}
