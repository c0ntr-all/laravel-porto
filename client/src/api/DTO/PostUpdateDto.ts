export interface IPostUpdateDto {
  title?: string
  content?: string
  date?: string
  time?: string | null
  tags?: string[]
  new_tags?: string[]
  deleted_attachments_ids?: string[]
}
