export interface IPresetCreateDto {
  title: string
  description?: string | null
  start_date: string | null
  end_date?: string | null
  color: string
  icon?: string | null
}
