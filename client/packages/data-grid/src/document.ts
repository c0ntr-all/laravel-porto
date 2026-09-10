import { cloneJson } from './clone'
import { createId } from './ids'
import {
  DATA_GRID_SCHEMA_VERSION,
  type DataGridCell,
  type DataGridColumn,
  type DataGridDocument,
  type DataGridMeta,
  type DataGridRow
} from './types'

const DEFAULT_COLUMN_TYPE = 'text'

function asRecord(value: unknown): Record<string, unknown> | null {
  return value !== null && typeof value === 'object' && !Array.isArray(value)
    ? value as Record<string, unknown>
    : null
}

function parseCell(value: unknown): DataGridCell {
  const record = asRecord(value)
  if (!record) {
    return { value: value == null ? '' : String(value) }
  }

  const cellValue = record.value
  if (
    cellValue === null ||
    typeof cellValue === 'string' ||
    typeof cellValue === 'number' ||
    typeof cellValue === 'boolean'
  ) {
    return { value: cellValue }
  }

  return { value: cellValue == null ? '' : String(cellValue) }
}

function parseColumn(value: unknown, index: number): DataGridColumn {
  const record = asRecord(value) ?? {}
  const title = typeof record.title === 'string' && record.title.trim()
    ? record.title
    : `Column ${index + 1}`

  return {
    id: typeof record.id === 'string' && record.id ? record.id : createId('col'),
    title,
    type: typeof record.type === 'string' && record.type ? record.type : DEFAULT_COLUMN_TYPE,
    width: typeof record.width === 'number' ? record.width : null,
    settings: asRecord(record.settings) ?? {}
  }
}

function parseRow(value: unknown): DataGridRow {
  const record = asRecord(value) ?? {}
  const rawCells = asRecord(record.cells) ?? {}
  const cells: Record<string, DataGridCell> = {}

  Object.entries(rawCells).forEach(([columnId, cell]) => {
    cells[columnId] = parseCell(cell)
  })

  return {
    id: typeof record.id === 'string' && record.id ? record.id : createId('row'),
    cells
  }
}

function parseMeta(value: unknown): DataGridMeta {
  const record = asRecord(value) ?? {}

  return {
    id: typeof record.id === 'string' && record.id ? record.id : createId('grid'),
    title: typeof record.title === 'string' ? record.title : '',
    templateId: typeof record.templateId === 'string' ? record.templateId : null
  }
}

export function createEmptyDataGrid(options?: {
  columns?: number
  rows?: number
  columnTitle?: string
}): DataGridDocument {
  const columnCount = Math.max(1, options?.columns ?? 1)
  const rowCount = Math.max(0, options?.rows ?? 1)
  const columnTitle = options?.columnTitle ?? 'Column'

  const columns = Array.from({ length: columnCount }, (_, index) => ({
    id: createId('col'),
    title: `${columnTitle} ${index + 1}`,
    type: DEFAULT_COLUMN_TYPE,
    width: null,
    settings: {}
  }))

  const rows = Array.from({ length: rowCount }, () => ({
    id: createId('row'),
    cells: {} as Record<string, DataGridCell>
  }))

  return {
    version: DATA_GRID_SCHEMA_VERSION,
    meta: {
      id: createId('grid'),
      title: '',
      templateId: null
    },
    columns,
    rows
  }
}

export function normalizeDataGrid(input: unknown): DataGridDocument {
  const record = asRecord(input) ?? {}
  const columnsSource = Array.isArray(record.columns) ? record.columns : []
  const rowsSource = Array.isArray(record.rows) ? record.rows : []
  const columns = columnsSource.length
    ? columnsSource.map(parseColumn)
    : createEmptyDataGrid().columns

  return {
    version: typeof record.version === 'number' ? record.version : DATA_GRID_SCHEMA_VERSION,
    meta: parseMeta(record.meta),
    columns,
    rows: rowsSource.map(parseRow)
  }
}

export function parseDataGrid(input: unknown): DataGridDocument {
  if (typeof input === 'string') {
    if (!input.trim()) {
      return createEmptyDataGrid()
    }

    return normalizeDataGrid(JSON.parse(input))
  }

  if (input == null) {
    return createEmptyDataGrid()
  }

  return normalizeDataGrid(input)
}

export function serializeDataGrid(document: DataGridDocument): string {
  return JSON.stringify(cloneJson(document))
}

export function getCellValue(row: DataGridRow, columnId: string): DataGridCell['value'] {
  const cell = row.cells[columnId]
  return cell ? cell.value : ''
}
