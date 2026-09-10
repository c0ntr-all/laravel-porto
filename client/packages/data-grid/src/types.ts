export const DATA_GRID_SCHEMA_VERSION = 1 as const

export type CellValue = string | number | boolean | null

export interface DataGridCell {
  value: CellValue
}

export interface DataGridColumn {
  id: string
  title: string
  /**
   * Renderer/type id. Built-in value is `text`.
   * Reserved for future date/boolean/number renderers from app settings.
   */
  type: string
  width?: number | null
  settings?: Record<string, unknown>
}

export interface DataGridRow {
  id: string
  cells: Record<string, DataGridCell>
}

export interface DataGridMeta {
  id: string
  title?: string
  templateId?: string | null
}

export interface DataGridDocument {
  version: number
  meta: DataGridMeta
  columns: DataGridColumn[]
  rows: DataGridRow[]
}

export interface DataGridLabels {
  addColumn: string
  addRow: string
  untitledColumn: string
  deleteColumn: string
  deleteRow: string
}

export const defaultDataGridLabels: DataGridLabels = {
  addColumn: 'Add column',
  addRow: 'Add row',
  untitledColumn: 'Column',
  deleteColumn: 'Delete column',
  deleteRow: 'Delete row'
}
