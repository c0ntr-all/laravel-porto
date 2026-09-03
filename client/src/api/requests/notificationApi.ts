import { api } from 'src/boot/axios'
import { IJsonApiResponse } from 'src/types'
import { INotificationListQuery } from 'src/types/notification'

const BASE = 'v1/app/notifications'

export const notificationApi = {
  async list(query: INotificationListQuery = {}): Promise<IJsonApiResponse> {
    const response = await api.get<IJsonApiResponse>(BASE, {
      params: {
        page: query.page,
        per_page: query.per_page,
        unread_only: query.unread_only ? 1 : undefined
      }
    })

    return response.data
  },

  async unreadCount(): Promise<{ unread_count: number }> {
    const response = await api.get<{ data: { unread_count: number } }>(`${BASE}/unread-count`)
    return response.data.data
  },

  async markAsRead(id: string): Promise<IJsonApiResponse> {
    const response = await api.patch<IJsonApiResponse>(`${BASE}/${id}/read`)
    return response.data
  },

  async markAllAsRead(): Promise<{ updated_count: number }> {
    const response = await api.patch<{ data: { updated_count: number } }>(`${BASE}/read-all`)
    return response.data.data
  }
}
