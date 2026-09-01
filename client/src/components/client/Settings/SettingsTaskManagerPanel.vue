<template>
  <div class="settings-panel">
    <div class="settings-panel__intro">
      <div class="settings-panel__title">Task Manager</div>
      <p class="settings-panel__text">
        Настройки отображения задач: доска списков или общий список.
      </p>
    </div>

    <div class="settings-panel__block">
      <div class="settings-panel__label">Вид задач по умолчанию</div>
      <q-btn-toggle
        :model-value="settings.taskManager.defaultViewMode"
        no-caps
        unelevated
        toggle-color="primary"
        color="grey-3"
        text-color="grey-8"
        :options="viewModeOptions"
        @update:model-value="updateTaskManager({ defaultViewMode: $event })"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { storeToRefs } from 'pinia'
import { useSettingsStore } from 'src/stores/modules/settingsStore'
import { TasksViewModeEnum } from 'src/enums/TaskManager/TasksViewModeEnum'

const settingsStore = useSettingsStore()
const { settings } = storeToRefs(settingsStore)
const { updateTaskManager } = settingsStore

const viewModeOptions = [
  { label: 'Блоки', value: TasksViewModeEnum.BLOCKS, icon: 'view_column' },
  { label: 'Список', value: TasksViewModeEnum.LIST, icon: 'view_list' }
]
</script>
