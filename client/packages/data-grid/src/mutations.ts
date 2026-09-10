import { cloneJson } from './clone'
import { createEmptyDataGrid } from './document'
import { createId } from './ids'
import type { CellValue, DataGridDocument } from './types'

function nextColumnTitle(document: DataGridDocument, untitledColumn: string): string {
  return `${untitledColumn} ${document.columns.length + 1}`
}

export function addColumn(
  document: DataGridDocument,
  options?: { title?: string; type?: string; untitledColumn?: string }
): DataGridDocument {
  const next = cloneJson(document)

  next.columns.push({
    id: createId('col'),
    title: options?.title?.trim() || nextColumnTitle(document, options?.untitledColumn ?? 'Column'),
    type: options?.type ?? 'text',
    width: null,
    settings: {}
  })

  return next
}

export function renameColumn(
  document: DataGridDocument,
  columnId: string,
  title: string
): DataGridDocument {
  const next = cloneJson(document)
  const column = next.columns.find((item) => item.id === columnId)

  if (column) {
    column.title = title.trim() || column.title
  }

  return next
}

export function removeColumn(document: DataGridDocument, columnId: string): DataGridDocument {
  if (document.columns.length <= 1) {
    return document
  }

  const next = cloneJson(document)
  next.columns = next.columns.filter((column) => column.id !== columnId)
  next.rows = next.rows.map((row) => {
    const cells = { ...row.cells }
    delete cells[columnId]
    return { ...row, cells }
  })

  return next
}

export function addRow(document: DataGridDocument): DataGridDocument {
  const next = cloneJson(document)
  next.rows.push({
    id: createId('row'),
    cells: {}
  })
  return next
}

export function removeRow(document: DataGridDocument, rowId: string): DataGridDocument {
  const next = cloneJson(document)
  next.rows = next.rows.filter((row) => row.id !== rowId)
  return next
}

export function setCellValue(
  document: DataGridDocument,
  rowId: string,
  columnId: string,
  value: CellValue
): DataGridDocument {
  const next = cloneJson(document)
  const row = next.rows.find((item) => item.id === rowId)

  if (row) {
    row.cells[columnId] = { value }
  }

  return next
}

export function replaceDocument(document: DataGridDocument | null | undefined): DataGridDocument {
  return document ? cloneJson(document) : createEmptyDataGrid()
}
