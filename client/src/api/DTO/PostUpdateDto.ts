export interface IPostUpdateDto {
  title?: string
  content?: string
  datetime: string | null
  tags?: string[]
  new_tags?: string[]
}
