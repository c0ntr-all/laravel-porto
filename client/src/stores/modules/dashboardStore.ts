import { computed, ref } from 'vue'
import { defineStore } from 'pinia'
import { dashboardApi } from 'src/api/requests/dashboardApi'
import {
  mapDashboardResponse,
  mapDashboardsResponse,
  mapWidgetResponse,
  mapWidgetTypes,
  mapWidgetsResponse
} from 'src/api/mappers/dashboard.mapper'
import { handleApiError, handleApiSuccess } from 'src/utils/jsonapi'
import {
  IDashboard,
  IDashboardCreatePayload,
  IDashboardUpdatePayload,
  IDashboardWidget,
  IWidgetCreatePayload,
  IWidgetType,
  IWidgetUpdatePayload
} from 'src/types/Dashboard/dashboard'

export const useDashboardStore = defineStore('dashboard', () => {
  const dashboards = ref<IDashboard[]>([])
  const current = ref<IDashboard | null>(null)
  const catalog = ref<IWidgetType[]>([])
  const isLoading = ref(false)
  const isSaving = ref(false)
  const isEditing = ref(false)

  const currentId = computed(() => current.value?.id ?? null)
  const widgets = computed(() => current.value?.widgets ?? [])

  function replaceDashboard(incoming: IDashboard): void {
    const index = dashboards.value.findIndex(item => item.id === incoming.id)
    if (index === -1) {
      dashboards.value = [...dashboards.value, incoming]
    } else {
      dashboards.value.splice(index, 1, { ...dashboards.value[index], ...incoming })
    }

    if (current.value?.id === incoming.id) {
      current.value = incoming
    }
  }

  async function loadDashboards(): Promise<void> {
    isLoading.value = true
    try {
      const listed = mapDashboardsResponse(await dashboardApi.listDashboards())
      dashboards.value = listed

      const preferred = listed.find(item => item.is_default) ?? listed[0] ?? null
      if (preferred) {
        await selectDashboard(preferred.id)
      } else {
        current.value = null
      }
    } catch (error) {
      handleApiError(error)
    } finally {
      isLoading.value = false
    }
  }

  async function selectDashboard(id: string): Promise<void> {
    isLoading.value = true
    try {
      current.value = mapDashboardResponse(await dashboardApi.getDashboard(id))
      replaceDashboard(current.value)
    } catch (error) {
      handleApiError(error)
    } finally {
      isLoading.value = false
    }
  }

  async function loadCatalog(): Promise<void> {
    if (catalog.value.length) {
      return
    }

    try {
      catalog.value = mapWidgetTypes(await dashboardApi.listWidgetTypes())
    } catch (error) {
      handleApiError(error)
    }
  }

  async function createDashboard(payload: IDashboardCreatePayload): Promise<IDashboard | null> {
    isSaving.value = true
    try {
      const created = mapDashboardResponse(await dashboardApi.createDashboard(payload))
      dashboards.value = [...dashboards.value, created]
      current.value = created
      handleApiSuccess({ meta: { message: 'Дашборд создан' } })
      await selectDashboard(created.id)
      return created
    } catch (error) {
      handleApiError(error)
      return null
    } finally {
      isSaving.value = false
    }
  }

  async function updateDashboard(id: string, payload: IDashboardUpdatePayload): Promise<void> {
    isSaving.value = true
    try {
      const updated = mapDashboardResponse(await dashboardApi.updateDashboard(id, payload))
      if (current.value?.id === id) {
        updated.widgets = current.value.widgets
      }
      replaceDashboard(updated)
      handleApiSuccess({ meta: { message: 'Дашборд обновлён' } })
    } catch (error) {
      handleApiError(error)
    } finally {
      isSaving.value = false
    }
  }

  async function deleteDashboard(id: string): Promise<void> {
    isSaving.value = true
    try {
      const response = await dashboardApi.deleteDashboard(id)
      dashboards.value = dashboards.value.filter(item => item.id !== id)
      handleApiSuccess(response)
      const next = dashboards.value[0]
      if (next) {
        await selectDashboard(next.id)
      } else {
        await loadDashboards()
      }
    } catch (error) {
      handleApiError(error)
    } finally {
      isSaving.value = false
    }
  }

  async function setDefault(id: string): Promise<void> {
    try {
      const updated = mapDashboardResponse(await dashboardApi.setDefaultDashboard(id))
      dashboards.value = dashboards.value.map(item => ({
        ...item,
        is_default: item.id === id
      }))
      if (current.value?.id === id) {
        updated.widgets = current.value.widgets
        current.value = { ...current.value, is_default: true }
      }
      replaceDashboard({ ...updated, widgets: current.value?.id === id ? current.value.widgets : updated.widgets })
      handleApiSuccess({ meta: { message: 'Дашборд по умолчанию обновлён' } })
    } catch (error) {
      handleApiError(error)
    }
  }

  async function createWidget(payload: IWidgetCreatePayload): Promise<void> {
    if (!current.value) {
      return
    }

    isSaving.value = true
    try {
      const widget = mapWidgetResponse(
        await dashboardApi.createWidget(current.value.id, payload)
      )
      current.value = {
        ...current.value,
        widgets: [...current.value.widgets, widget],
        widgets_count: current.value.widgets.length + 1
      }
      handleApiSuccess({ meta: { message: 'Виджет добавлен' } })
    } catch (error) {
      handleApiError(error)
    } finally {
      isSaving.value = false
    }
  }

  async function updateWidget(widgetId: string, payload: IWidgetUpdatePayload): Promise<void> {
    if (!current.value) {
      return
    }

    try {
      const widget = mapWidgetResponse(
        await dashboardApi.updateWidget(current.value.id, widgetId, payload)
      )
      current.value = {
        ...current.value,
        widgets: current.value.widgets.map(item => item.id === widgetId ? widget : item)
      }
    } catch (error) {
      handleApiError(error)
    }
  }

  async function deleteWidget(widgetId: string): Promise<void> {
    if (!current.value) {
      return
    }

    try {
      const response = await dashboardApi.deleteWidget(current.value.id, widgetId)
      current.value = {
        ...current.value,
        widgets: current.value.widgets.filter(item => item.id !== widgetId),
        widgets_count: Math.max(0, current.value.widgets_count - 1)
      }
      handleApiSuccess(response)
    } catch (error) {
      handleApiError(error)
    }
  }

  async function moveWidget(widgetId: string, direction: -1 | 1): Promise<void> {
    if (!current.value) {
      return
    }

    const ordered = [...current.value.widgets]
    const index = ordered.findIndex(item => item.id === widgetId)
    const target = index + direction
    if (index < 0 || target < 0 || target >= ordered.length) {
      return
    }

    const [moved] = ordered.splice(index, 1)
    ordered.splice(target, 0, moved as IDashboardWidget)
    current.value = { ...current.value, widgets: ordered }

    try {
      const widgets = mapWidgetsResponse(
        await dashboardApi.reorderWidgets(current.value.id, ordered.map(item => item.id))
      )
      current.value = { ...current.value, widgets }
    } catch (error) {
      handleApiError(error)
      await selectDashboard(current.value.id)
    }
  }

  return {
    dashboards,
    current,
    catalog,
    isLoading,
    isSaving,
    isEditing,
    currentId,
    widgets,
    loadDashboards,
    selectDashboard,
    loadCatalog,
    createDashboard,
    updateDashboard,
    deleteDashboard,
    setDefault,
    createWidget,
    updateWidget,
    deleteWidget,
    moveWidget
  }
})
