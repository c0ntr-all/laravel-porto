<template>
  <div class="settings-panel">
    <div class="settings-panel__intro">
      <div class="settings-panel__title">Dashboard</div>
      <p class="settings-panel__text">
        Состав и размер виджетов настраиваются на странице Dashboard.
        Здесь — плотность сетки по умолчанию.
      </p>
    </div>

    <q-list class="settings-panel__list" separator>
      <q-item tag="label">
        <q-item-section>
          <q-item-label>Компактные виджеты</q-item-label>
          <q-item-label caption>Более плотная сетка на главной</q-item-label>
        </q-item-section>
        <q-item-section side>
          <q-toggle
            :model-value="settings.dashboard.compactWidgets"
            color="primary"
            @update:model-value="updateDashboard({ compactWidgets: $event })"
          />
        </q-item-section>
      </q-item>
    </q-list>
  </div>
</template>

<script setup lang="ts">
import { storeToRefs } from 'pinia'
import { useSettingsStore } from 'src/stores/modules/settingsStore'

const settingsStore = useSettingsStore()
const { settings } = storeToRefs(settingsStore)
const { updateDashboard } = settingsStore
</script>
