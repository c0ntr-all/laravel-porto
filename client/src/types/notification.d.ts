export interface INotification {
  id: string
  type: string
  title: string
  body: string
  data: Record<string, unknown>
  is_read: boolean
  read_at: string | null
  created_at: string
}

export interface INotificationListMeta {
  current_page: number
  last_page: number
  per_page: number
  total: number
}

export interface INotificationListResult {
  items: INotification[]
  meta: INotificationListMeta
}

export interface INotificationListQuery {
  page?: number
  per_page?: number
  unread_only?: boolean
}

export interface INotificationCreatedPayload {
  id: string
  type: string
  title: string
  body: string
  data?: Record<string, unknown>
  is_read: boolean
  read_at: string | null
  created_at: string
  unread_count: number
}

export interface INotificationReadPayload {
  notification_id: string | null
  unread_count: number
}
