import { computed, ref, watch, type Ref } from 'vue'
import { cloneJson } from '../clone'
import { createEmptyDataGrid, normalizeDataGrid, parseDataGrid, serializeDataGrid } from '../document'
import {
  addColumn as addColumnMutation,
  addRow as addRowMutation,
  removeColumn as removeColumnMutation,
  removeRow as removeRowMutation,
  renameColumn as renameColumnMutation,
  setCellValue as setCellValueMutation
} from '../mutations'
import type { CellValue, DataGridDocument } from '../types'

export interface UseDataGridOptions {
  initial?: unknown
  untitledColumn?: string
}

export function useDataGrid(
  source?: Ref<DataGridDocument | undefined>,
  options: UseDataGridOptions = {}
) {
  const document = ref<DataGridDocument>(
    source?.value
      ? normalizeDataGrid(source.value)
      : parseDataGrid(options.initial ?? createEmptyDataGrid({ columnTitle: options.untitledColumn }))
  )

  let lastEmitted = serializeDataGrid(document.value)

  watch(() => source?.value, (value) => {
    if (!value) {
      return
    }

    const serialized = serializeDataGrid(normalizeDataGrid(value))
    if (serialized !== lastEmitted) {
      document.value = normalizeDataGrid(value)
      lastEmitted = serialized
    }
  }, { deep: true })

  const columns = computed(() => document.value.columns)
  const rows = computed(() => document.value.rows)

  function commit(next: DataGridDocument) {
    document.value = next
    lastEmitted = serializeDataGrid(next)
    return cloneJson(next)
  }

  function addColumn(title?: string) {
    return commit(addColumnMutation(document.value, {
      title,
      untitledColumn: options.untitledColumn
    }))
  }

  function renameColumn(columnId: string, title: string) {
    return commit(renameColumnMutation(document.value, columnId, title))
  }

  function removeColumn(columnId: string) {
    return commit(removeColumnMutation(document.value, columnId))
  }

  function addRow() {
    return commit(addRowMutation(document.value))
  }

  function removeRow(rowId: string) {
    return commit(removeRowMutation(document.value, rowId))
  }

  function setCellValue(rowId: string, columnId: string, value: CellValue) {
    return commit(setCellValueMutation(document.value, rowId, columnId, value))
  }

  function load(input: unknown) {
    return commit(parseDataGrid(input))
  }

  function reset() {
    return commit(createEmptyDataGrid({ columnTitle: options.untitledColumn }))
  }

  function toJSON() {
    return cloneJson(document.value)
  }

  function toJSONString() {
    return serializeDataGrid(document.value)
  }

  return {
    document,
    columns,
    rows,
    addColumn,
    renameColumn,
    removeColumn,
    addRow,
    removeRow,
    setCellValue,
    load,
    reset,
    toJSON,
    toJSONString
  }
}
