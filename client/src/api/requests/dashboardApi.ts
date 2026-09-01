import { api } from 'src/boot/axios'
import { IJsonApiResponse } from 'src/types'
import {
  IDashboardCreatePayload,
  IDashboardUpdatePayload,
  IWidgetCreatePayload,
  IWidgetType,
  IWidgetUpdatePayload
} from 'src/types/Dashboard/dashboard'

export const dashboardApi = {
  async listDashboards(): Promise<IJsonApiResponse> {
    const response = await api.get('v1/dashboards')
    return response.data
  },

  async getDashboard(id: string): Promise<IJsonApiResponse> {
    const response = await api.get(`v1/dashboards/${id}`)
    return response.data
  },

  async createDashboard(payload: IDashboardCreatePayload): Promise<IJsonApiResponse> {
    const response = await api.post('v1/dashboards', payload)
    return response.data
  },

  async updateDashboard(id: string, payload: IDashboardUpdatePayload): Promise<IJsonApiResponse> {
    const response = await api.patch(`v1/dashboards/${id}`, payload)
    return response.data
  },

  async deleteDashboard(id: string): Promise<{ meta?: { message?: string } }> {
    const response = await api.delete(`v1/dashboards/${id}`)
    return response.data
  },

  async setDefaultDashboard(id: string): Promise<IJsonApiResponse> {
    const response = await api.post(`v1/dashboards/${id}/default`)
    return response.data
  },

  async listWidgetTypes(): Promise<{ data: IWidgetType[]; meta?: { count?: number } }> {
    const response = await api.get('v1/dashboard/widget-types')
    return response.data
  },

  async createWidget(dashboardId: string, payload: IWidgetCreatePayload): Promise<IJsonApiResponse> {
    const response = await api.post(`v1/dashboards/${dashboardId}/widgets`, payload)
    return response.data
  },

  async updateWidget(
    dashboardId: string,
    widgetId: string,
    payload: IWidgetUpdatePayload
  ): Promise<IJsonApiResponse> {
    const response = await api.patch(`v1/dashboards/${dashboardId}/widgets/${widgetId}`, payload)
    return response.data
  },

  async deleteWidget(dashboardId: string, widgetId: string): Promise<{ meta?: { message?: string } }> {
    const response = await api.delete(`v1/dashboards/${dashboardId}/widgets/${widgetId}`)
    return response.data
  },

  async reorderWidgets(dashboardId: string, ids: string[]): Promise<IJsonApiResponse> {
    const response = await api.post(`v1/dashboards/${dashboardId}/widgets/reorder`, {
      ids: ids.map(id => Number(id))
    })
    return response.data
  }
}
