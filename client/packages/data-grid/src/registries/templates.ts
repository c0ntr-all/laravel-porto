import type { DataGridDocument } from '../types'

export interface DataGridTemplate {
  id: string
  name: string
  description?: string
  create: () => DataGridDocument
}

const templates = new Map<string, DataGridTemplate>()

export const templateRegistry = {
  register(template: DataGridTemplate) {
    templates.set(template.id, template)
  },

  get(id: string): DataGridTemplate | undefined {
    return templates.get(id)
  },

  list(): DataGridTemplate[] {
    return [...templates.values()]
  }
}
