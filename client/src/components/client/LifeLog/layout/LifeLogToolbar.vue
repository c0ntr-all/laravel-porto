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

    <div class="row items-center q-gutter-sm">
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

      <q-btn
        flat
        round
        dense
        :color="showTimeline ? 'primary' : 'grey-6'"
        icon="account_tree"
        @click="emit('update:showTimeline', !showTimeline)"
      >
        <q-tooltip>Граф диапазонов</q-tooltip>
      </q-btn>
    </div>
  </div>
</template>

<script setup lang="ts">
import { LifeLogViewMode } from 'src/types'
import { LifeLogViewModeEnum } from 'src/enums/LifeLog/LifeLogViewModeEnum'

defineProps<{
  viewMode: LifeLogViewMode
  showTimeline: boolean
}>()

const emit = defineEmits<{
  'create-post': []
  'update:viewMode': [value: LifeLogViewMode]
  'update:showTimeline': [value: boolean]
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
