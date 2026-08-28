export interface IMusicTag {
  id: string
  name: string
  content?: string | null
  is_base?: boolean
  parent_id?: string | null
}
