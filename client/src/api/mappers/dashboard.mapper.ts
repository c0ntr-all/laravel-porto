import { mapEntity, mapResponse } from 'src/utils/jsonApiMapper'
import { IJsonApiResponse } from 'src/types'
import {
  IDashboard,
  IDashboardWidget,
  IWidgetPayload,
  IWidgetType
} from 'src/types/Dashboard/dashboard'
import { WidgetSizeEnum } from 'src/enums/Dashboard/WidgetSizeEnum'

function asRecord(value: unknown): Record<string, any> {
  return value && typeof value === 'object' ? value as Record<string, any> : {}
}

function mapPayload(raw: unknown): IWidgetPayload | null {
  if (!raw || typeof raw !== 'object') {
    return null
  }

  const data = asRecord(raw)

  return {
    type: String(data.type ?? ''),
    title: String(data.title ?? ''),
    view: String(data.view ?? 'list'),
    data: asRecord(data.data),
    html: data.html == null ? null : String(data.html),
    meta: asRecord(data.meta),
    ok: data.ok !== false
  }
}

export function mapWidget(raw: Record<string, any>): IDashboardWidget {
  return {
    id: String(raw.id),
    dashboard_id: String(raw.dashboard_id ?? ''),
    type: String(raw.type ?? ''),
    title: raw.title == null ? null : String(raw.title),
    size: raw.size === WidgetSizeEnum.Full || raw.size === WidgetSizeEnum.Third
      ? raw.size
      : WidgetSizeEnum.Half,
    sort_order: Number(raw.sort_order ?? 0),
    config: asRecord(raw.config),
    is_enabled: Boolean(raw.is_enabled ?? true),
    payload: mapPayload(raw.payload),
    created_at: raw.created_at,
    updated_at: raw.updated_at
  }
}

export function mapDashboard(raw: Record<string, any>): IDashboard {
  const widgetsRaw = Array.isArray(raw.widgets) ? raw.widgets : []

  return {
    id: String(raw.id),
    name: String(raw.name ?? ''),
    description: raw.description == null ? null : String(raw.description),
    is_default: Boolean(raw.is_default),
    sort_order: Number(raw.sort_order ?? 0),
    widgets_count: Number(raw.widgets_count ?? widgetsRaw.length),
    widgets: widgetsRaw.map((item: Record<string, any>) => mapWidget(item)),
    created_at: raw.created_at,
    updated_at: raw.updated_at
  }
}

export function mapDashboardsResponse(response: IJsonApiResponse): IDashboard[] {
  return mapResponse(response).map(mapDashboard)
}

export function mapDashboardResponse(response: IJsonApiResponse): IDashboard {
  const resource = Array.isArray(response.data) ? response.data[0] : response.data
  return mapDashboard(mapEntity(resource, response.included))
}

export function mapWidgetsResponse(response: IJsonApiResponse): IDashboardWidget[] {
  return mapResponse(response).map(mapWidget)
}

export function mapWidgetResponse(response: IJsonApiResponse): IDashboardWidget {
  const resource = Array.isArray(response.data) ? response.data[0] : response.data
  return mapWidget(mapEntity(resource, response.included))
}

export function mapWidgetTypes(payload: { data?: IWidgetType[] }): IWidgetType[] {
  return Array.isArray(payload.data) ? payload.data : []
}
