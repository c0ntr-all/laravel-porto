<template>
  <div class="ll-posts-tabs">
    <q-tabs
      :model-value="modelValue"
      dense
      align="left"
      active-color="primary"
      indicator-color="primary"
      class="ll-posts-tabs__tabs text-grey-8"
      @update:model-value="onTabChange"
    >
      <q-tab
        v-for="tab in tabs"
        :key="tab.id"
        :name="tab.id"
        :label="tab.label"
        no-caps
      />
    </q-tabs>
    <q-separator />
  </div>
</template>

<script lang="ts" setup>
import { computed } from 'vue'
import {
  buildLifeLogPostsTabs,
  isLifeLogPostsTabId,
  LifeLogPostsTabId
} from 'src/constants/LifeLog/postsTabs'

defineProps<{
  modelValue: LifeLogPostsTabId
}>()

const emit = defineEmits<{
  'update:modelValue': [value: LifeLogPostsTabId]
}>()

const tabs = computed(() => buildLifeLogPostsTabs())

function onTabChange (value: string | number) {
  if (!isLifeLogPostsTabId(value)) {
    return
  }

  emit('update:modelValue', value)
}
</script>

<style scoped lang="scss">
.ll-posts-tabs {
  margin-bottom: 12px;
  background: #fff;
  border: 1px solid rgba(0, 0, 0, 0.08);
  border-radius: 8px;
  overflow: hidden;

  &__tabs {
    min-height: 44px;
  }
}
</style>
