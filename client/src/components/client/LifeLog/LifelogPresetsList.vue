<template>
  <div class="presets-list">
    <q-inner-loading :showing="isLoading">
      <q-spinner size="32px" color="primary" />
    </q-inner-loading>

    <q-list
      v-if="!isLoading && presets.length"
      bordered
      separator
      class="rounded-borders"
    >
      <q-item
        v-for="preset in presets"
        :key="preset.id"
      >
        <q-item-section avatar>
          <q-avatar
            size="sm"
            :style="{ backgroundColor: preset.color || '#ccc' }"
          />
        </q-item-section>

        <q-item-section>
          <q-item-label>{{ preset.title }}</q-item-label>
          <q-item-label caption>
            {{ formatPresetDateRange(preset.date_from, preset.date_to) }}
          </q-item-label>
          <q-item-label
            v-if="preset.description"
            caption
            class="q-mt-xs text-grey-7"
          >
            {{ preset.description }}
          </q-item-label>
        </q-item-section>
      </q-item>
    </q-list>

    <div
      v-else-if="!isLoading"
      class="text-center text-grey-6 q-pa-md presets-list__empty"
    >
      Preset'ов пока нет
    </div>
  </div>
</template>

<script lang="ts" setup>
import { onMounted } from 'vue'
import { storeToRefs } from 'pinia'
import { usePresetStore } from 'src/stores/modules/presetStore'
import { formatPresetDateRange } from 'src/utils/datetime'

const presetStore = usePresetStore()
const { presets, isLoading } = storeToRefs(presetStore)

onMounted(() => {
  presetStore.getPresets()
})
</script>

<style lang="scss" scoped>
.presets-list {
  position: relative;
  min-height: 48px;

  &__empty {
    border: 1px dashed #ddd;
    border-radius: 4px;
  }
}
</style>
