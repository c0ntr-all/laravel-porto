<template>
  <div class="ll-toolbar row items-center justify-between q-mb-md">
    <div class="row items-center q-gutter-sm">
      <q-btn
        color="primary"
        icon="add"
        label="Создать пост"
        no-caps
        @click="emit('create-post')"
      />
    </div>

    <q-btn-toggle
      :model-value="viewMode"
      no-caps
      unelevated
      toggle-color="primary"
      color="grey-3"
      text-color="grey-8"
      :options="viewModeOptions"
      @update:model-value="emit('update:viewMode', $event)"
    />
  </div>
</template>

<script setup lang="ts">
import { LifeLogViewMode } from 'src/types'
import { LifeLogViewModeEnum } from 'src/enums/LifeLog/LifeLogViewModeEnum'

defineProps<{
  viewMode: LifeLogViewMode
}>()

const emit = defineEmits<{
  'create-post': []
  'update:viewMode': [value: LifeLogViewMode]
}>()

const viewModeOptions = [
  { label: 'Карточки', value: LifeLogViewModeEnum.Expanded, icon: 'view_agenda' },
  { label: 'Строки', value: LifeLogViewModeEnum.Compact, icon: 'view_list' }
]
</script>

<style scoped lang="scss">
.ll-toolbar {
  gap: 12px;
  flex-wrap: wrap;
}
</style>
