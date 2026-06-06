export interface IPeriodCreateDto {
  title: string
  description?: string | null
  start_post_id: string
  end_post_id?: string | null
  color: string
  icon?: string | null
}
