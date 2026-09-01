<template>
  <div class="settings-panel">
    <div class="settings-panel__intro">
      <div class="settings-panel__title">Lifelog</div>
      <p class="settings-panel__text">
        Настройки отображения постов: вид ленты и граф диапазонов.
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

    <q-list class="settings-panel__list" separator>
      <q-item tag="label">
        <q-item-section>
          <q-item-label>Показывать граф диапазонов</q-item-label>
          <q-item-label caption>Боковая панель с presets и таймлайном</q-item-label>
        </q-item-section>
        <q-item-section side>
          <q-toggle
            :model-value="settings.lifelog.showTimeline"
            color="primary"
            @update:model-value="updateLifelog({ showTimeline: $event })"
          />
        </q-item-section>
      </q-item>
    </q-list>
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
