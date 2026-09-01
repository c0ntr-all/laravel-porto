<template>
  <div>
    <div v-if="isLoading && !current" class="row justify-center q-py-xl">
      <q-spinner color="primary" size="40px" />
    </div>

    <template v-else>
      <DashboardToolbar
        :dashboards="dashboards"
        :current-id="currentId"
        :editing="isEditing"
        :is-default="Boolean(current?.is_default)"
        @select="selectDashboard"
        @add-widget="openPicker"
        @create-dashboard="createOpen = true"
        @toggle-edit="isEditing = !isEditing"
        @rename="renameDashboard"
        @set-default="current && setDefault(current.id)"
        @remove="removeDashboard"
      />

      <div v-if="current" class="dashboard-grid" :class="{ 'dashboard-grid--compact': compact }">
        <DashboardWidgetCard
          v-for="widget in widgets"
          :key="widget.id"
          :widget="widget"
          :definition="definitionByType(widget.type)"
          :editing="isEditing"
          :compact="compact"
          @resize="updateWidget(widget.id, { size: $event as any })"
          @move="moveWidget(widget.id, $event)"
          @configure="openConfig(widget)"
          @remove="deleteWidget(widget.id)"
        />
      </div>

      <div v-if="current && !widgets.length" class="result-empty q-mt-xl">
        <div class="result-empty__content">
          <div class="result-empty__icon">
            <q-icon name="dashboard" size="32px" color="primary" />
          </div>
          <div class="result-empty__title">На этом дашборде пока нет виджетов</div>
          <q-btn class="q-mt-md" color="primary" unelevated label="Добавить виджет" @click="openPicker" />
        </div>
      </div>
    </template>

    <DashboardWidgetPicker v-model="pickerOpen" :catalog="catalog" @select="addFromCatalog" />
    <DashboardCreateDialog v-model="createOpen" @create="createDashboard" />
    <DashboardWidgetConfigDialog
      v-model="configOpen"
      :widget="configuredWidget"
      :definition="configuredWidget ? definitionByType(configuredWidget.type) : undefined"
      @save="saveConfig"
    />
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { storeToRefs } from 'pinia'
import { useQuasar } from 'quasar'
import { useDashboardStore } from 'src/stores/modules/dashboardStore'
import { useSettingsStore } from 'src/stores/modules/settingsStore'
import { IDashboardWidget, IWidgetType } from 'src/types/Dashboard/dashboard'
import DashboardToolbar from 'src/components/client/Dashboard/DashboardToolbar.vue'
import DashboardWidgetCard from 'src/components/client/Dashboard/DashboardWidgetCard.vue'
import DashboardWidgetPicker from 'src/components/client/Dashboard/DashboardWidgetPicker.vue'
import DashboardCreateDialog from 'src/components/client/Dashboard/DashboardCreateDialog.vue'
import DashboardWidgetConfigDialog from 'src/components/client/Dashboard/DashboardWidgetConfigDialog.vue'

const $q = useQuasar()
const dashboardStore = useDashboardStore()
const settingsStore = useSettingsStore()
const { dashboards, current, catalog, isLoading, isEditing, currentId, widgets } = storeToRefs(dashboardStore)
const {
  loadDashboards,
  loadCatalog,
  selectDashboard,
  createDashboard,
  updateDashboard,
  deleteDashboard,
  setDefault,
  createWidget,
  updateWidget,
  deleteWidget,
  moveWidget
} = dashboardStore

const compact = computed(() => settingsStore.settings.dashboard.compactWidgets)
const pickerOpen = ref(false)
const createOpen = ref(false)
const configOpen = ref(false)
const configuredWidget = ref<IDashboardWidget | null>(null)

function definitionByType(type: string): IWidgetType | undefined {
  return catalog.value.find(item => item.type === type)
}

async function openPicker(): Promise<void> {
  await loadCatalog()
  pickerOpen.value = true
}

function addFromCatalog(item: IWidgetType): void {
  pickerOpen.value = false
  void createWidget({
    type: item.type,
    size: item.default_size,
    config: Object.fromEntries(
      Object.entries(item.config_schema).map(([key, field]) => [key, field.default ?? null])
    )
  })
}

function openConfig(widget: IDashboardWidget): void {
  configuredWidget.value = widget
  configOpen.value = true
}

function saveConfig(payload: { title: string | null; config: Record<string, any> }): void {
  if (!configuredWidget.value) {
    return
  }

  void updateWidget(configuredWidget.value.id, payload)
}

function renameDashboard(): void {
  if (!current.value) {
    return
  }

  $q.dialog({
    title: 'Переименовать дашборд',
    prompt: {
      model: current.value.name,
      type: 'text'
    },
    cancel: true,
    ok: 'Сохранить'
  }).onOk((name: string) => {
    if (name.trim()) {
      void updateDashboard(current.value!.id, { name: name.trim() })
    }
  })
}

function removeDashboard(): void {
  if (!current.value) {
    return
  }

  $q.dialog({
    title: 'Удалить дашборд?',
    message: `«${current.value.name}» будет удалён вместе с виджетами.`,
    cancel: true,
    ok: { label: 'Удалить', color: 'negative' }
  }).onOk(() => {
    void deleteDashboard(current.value!.id)
  })
}

onMounted(async () => {
  await Promise.all([loadDashboards(), loadCatalog()])
})
</script>

<style lang="scss" scoped>
.dashboard-grid {
  display: grid;
  grid-template-columns: repeat(12, minmax(0, 1fr));
  gap: 16px;

  &--compact {
    gap: 10px;
  }
}
</style>
