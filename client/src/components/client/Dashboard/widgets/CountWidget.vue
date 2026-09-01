<template>
  <div class="count-widget">
    <div class="count-widget__value">{{ value }}</div>
    <div class="count-widget__label">{{ label }}</div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { IWidgetPayload } from 'src/types/Dashboard/dashboard'

const props = defineProps<{
  payload: IWidgetPayload | null
}>()

const value = computed(() => Number(props.payload?.data?.value ?? props.payload?.meta?.count ?? 0))
const label = computed(() => String(props.payload?.data?.label ?? ''))
</script>

<style lang="scss" scoped>
.count-widget {
  display: flex;
  flex-direction: column;
  justify-content: center;
  min-height: 88px;

  &__value {
    font-size: 36px;
    font-weight: 700;
    color: #6C5FFC;
    line-height: 1.1;
  }

  &__label {
    margin-top: 4px;
    color: #777a8f;
    font-size: 13px;
  }
}
</style>
