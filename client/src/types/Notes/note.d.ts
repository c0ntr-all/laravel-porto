export interface INoteLink {
  id: string
  name: string | null
  label: string
}

export interface INote {
  id: string
  name: string
  content: string | null
  content_raw: string | null
  excerpt: string
  parent_id: string | null
  children_count: number
  is_pinned: boolean
  pin_position: number | null
  wiki_links: INoteLink[]
  backlinks: INoteLink[]
  unresolved_links: string[]
  ambiguous_links: string[]
  created_at: string | null
  updated_at: string
}

export interface INoteListQuery {
  cursor?: string | null
  perPage?: number
  sort?: string
  text?: string
  parentId?: number | null
  pinned?: 0 | 1
  roots?: boolean
}

export interface INoteCreatePayload {
  name: string
  content?: string | null
  parent_id?: number | null
}

export interface INoteUpdatePayload {
  updated_at: string
  name?: string
  content?: string | null
  parent_id?: number | null
}
