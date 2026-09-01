<template>
  <div class="settings-panel">
    <div class="settings-panel__intro">
      <div class="settings-panel__title">Gallery</div>
      <p class="settings-panel__text">
        Плотность сетки альбомов на странице галереи.
      </p>
    </div>

    <div class="settings-panel__block">
      <div class="settings-panel__label">Размер карточек</div>
      <q-btn-toggle
        :model-value="settings.gallery.gridSize"
        no-caps
        unelevated
        toggle-color="primary"
        color="grey-3"
        text-color="grey-8"
        :options="gridOptions"
        @update:model-value="updateGallery({ gridSize: $event })"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { storeToRefs } from 'pinia'
import { useSettingsStore } from 'src/stores/modules/settingsStore'

const settingsStore = useSettingsStore()
const { settings } = storeToRefs(settingsStore)
const { updateGallery } = settingsStore

const gridOptions = [
  { label: 'Компактный', value: 's', icon: 'grid_view' },
  { label: 'Обычный', value: 'm', icon: 'apps' },
  { label: 'Крупный', value: 'l', icon: 'crop_square' }
]
</script>
