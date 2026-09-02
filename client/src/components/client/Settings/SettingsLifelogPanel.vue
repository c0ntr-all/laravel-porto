<template>
  <div class="settings-panel">
    <div class="settings-panel__intro">
      <div class="settings-panel__title">Lifelog</div>
      <p class="settings-panel__text">
        Настройки отображения постов и вида ленты.
      </p>
    </div>

    <div class="settings-panel__block">
      <div class="settings-panel__label">Вид постов по умолчанию</div>
      <q-btn-toggle
        :model-value="settings.lifelog.defaultViewMode"
        no-caps
        unelevated
        toggle-color="primary"
        color="grey-3"
        text-color="grey-8"
        :options="viewModeOptions"
        @update:model-value="updateLifelog({ defaultViewMode: $event })"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { storeToRefs } from 'pinia'
import { useSettingsStore } from 'src/stores/modules/settingsStore'
import { LifeLogViewModeEnum } from 'src/enums/LifeLog/LifeLogViewModeEnum'

const settingsStore = useSettingsStore()
const { settings } = storeToRefs(settingsStore)
const { updateLifelog } = settingsStore

const viewModeOptions = [
  { label: 'Карточки', value: LifeLogViewModeEnum.Expanded, icon: 'view_agenda' },
  { label: 'Строки', value: LifeLogViewModeEnum.Compact, icon: 'view_list' }
]
</script>
