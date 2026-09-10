import type { Component } from 'vue'
import type { CellValue, DataGridColumn, DataGridRow } from '../types'

export interface CellRendererContext {
  column: DataGridColumn
  row: DataGridRow
  value: CellValue
  readonly: boolean
}

export interface CellRendererDefinition {
  type: string
  label: string
  defaultValue: () => CellValue
  component: Component
}

const renderers = new Map<string, CellRendererDefinition>()

export const cellRendererRegistry = {
  register(definition: CellRendererDefinition) {
    renderers.set(definition.type, definition)
  },

  get(type: string): CellRendererDefinition | undefined {
    return renderers.get(type)
  },

  resolve(type: string): CellRendererDefinition {
    return renderers.get(type) ?? renderers.get('text') as CellRendererDefinition
  },

  list(): CellRendererDefinition[] {
    return [...renderers.values()]
  }
}
