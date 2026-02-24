export interface IPostCreateDto {
  title?: string
  content?: string
  date: string
  time: string | null
  tags?: string[]
  new_tags?: string[]
  attachments?: {id: string, type: string}[]
}
