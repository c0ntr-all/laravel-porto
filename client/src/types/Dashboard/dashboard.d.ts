import { WidgetSize } from 'src/enums/Dashboard/WidgetSizeEnum'
import { WidgetView } from 'src/enums/Dashboard/WidgetViewEnum'

export interface IWidgetConfigField {
  type: 'integer' | 'boolean' | 'string' | 'text' | 'html' | string
  label?: string
  required?: boolean
  min?: number
  max?: number
  default?: unknown
}

export interface IWidgetPayload {
  type: string
  title: string
  view: WidgetView | string
  data: Record<string, any>
  html: string | null
  meta: Record<string, any>
  ok: boolean
}

export interface IWidgetItem {
  id: string
  title: string
  subtitle?: string | null
  image?: string | null
  href?: string | null
}

export interface IDashboardWidget {
  id: string
  dashboard_id: string
  type: string
  title: string | null
  size: WidgetSize
  sort_order: number
  config: Record<string, any>
  is_enabled: boolean
  payload: IWidgetPayload | null
  created_at?: string
  updated_at?: string
}

export interface IDashboard {
  id: string
  name: string
  description: string | null
  is_default: boolean
  sort_order: number
  widgets_count: number
  widgets: IDashboardWidget[]
  created_at?: string
  updated_at?: string
}

export interface IWidgetType {
  type: string
  name: string
  description: string
  category: string
  category_label: string
  icon: string
  view: string
  supported_sizes: WidgetSize[]
  default_size: WidgetSize
  config_schema: Record<string, IWidgetConfigField>
  is_static: boolean
}

export interface IDashboardCreatePayload {
  name: string
  description?: string | null
  is_default?: boolean
  with_defaults?: boolean
}

export interface IDashboardUpdatePayload {
  name?: string
  description?: string | null
  is_default?: boolean
}

export interface IWidgetCreatePayload {
  type: string
  title?: string | null
  size?: WidgetSize
  config?: Record<string, any>
}

export interface IWidgetUpdatePayload {
  title?: string | null
  size?: WidgetSize
  config?: Record<string, any>
  is_enabled?: boolean
}
