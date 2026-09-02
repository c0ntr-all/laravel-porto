<template>
  <div class="ll-preset-filter-chips">
    <div class="ll-preset-filter-chips__label text-caption">
      Фильтр по preset
    </div>

    <div class="row q-gutter-xs">
      <button
        v-for="preset in presets"
        :key="preset.id"
        type="button"
        class="ll-preset-filter-chips__chip"
        :class="{ 'll-preset-filter-chips__chip--active': activePresetId === preset.id }"
        @click="emit('apply', preset)"
      >
        <span
          class="ll-preset-filter-chips__dot"
          :style="{ backgroundColor: preset.color || '#90a4ae' }"
        />
        <span class="ll-preset-filter-chips__title">{{ preset.title }}</span>
      </button>

      <span v-if="!presets.length" class="text-caption text-grey-6">
        Нет presets
      </span>
    </div>
  </div>
</template>

<script setup lang="ts">
import { IPreset } from 'src/types'

defineProps<{
  presets: IPreset[]
  activePresetId: string | null
}>()

const emit = defineEmits<{
  apply: [preset: IPreset]
}>()
</script>

<style scoped lang="scss">
.ll-preset-filter-chips {
  &__label {
    color: #475569;
    margin-bottom: 8px;
  }

  &__chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    max-width: 100%;
    padding: 4px 10px;
    border: 1px solid #cbd5e1;
    border-radius: 16px;
    background: #fff;
    color: #1e293b;
    font-size: 12px;
    line-height: 1.3;
    cursor: pointer;
    transition: border-color 0.15s ease, background-color 0.15s ease, box-shadow 0.15s ease;

    &:hover {
      border-color: #94a3b8;
      background: #f8fafc;
    }

    &--active {
      border-color: #64748b;
      background: #f1f5f9;
      box-shadow: inset 0 0 0 1px rgba(100, 116, 139, 0.15);
    }
  }

  &__dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
  }

  &__title {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }
}
</style>
