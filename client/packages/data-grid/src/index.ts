import './registerBuiltins'

export { default as DataGrid } from './components/DataGrid.vue'
export { default as TextCell } from './components/TextCell.vue'
export { useDataGrid } from './composables/useDataGrid'
export {
  createEmptyDataGrid,
  getCellValue,
  normalizeDataGrid,
  parseDataGrid,
  serializeDataGrid
} from './document'
export { createId } from './ids'
export {
  addColumn,
  addRow,
  removeColumn,
  removeRow,
  renameColumn,
  setCellValue
} from './mutations'
export { cellRendererRegistry } from './registries/cellRenderers'
export { templateRegistry } from './registries/templates'
export { DATA_GRID_SCHEMA_VERSION, defaultDataGridLabels } from './types'
export type {
  CellValue,
  DataGridCell,
  DataGridColumn,
  DataGridDocument,
  DataGridLabels,
  DataGridMeta,
  DataGridRow
} from './types'
export type { CellRendererDefinition, CellRendererContext } from './registries/cellRenderers'
export type { DataGridTemplate } from './registries/templates'
