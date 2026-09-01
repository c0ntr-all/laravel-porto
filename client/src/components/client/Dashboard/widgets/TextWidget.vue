<template>
  <div class="text-widget">
    <p v-if="content" class="text-widget__content">{{ content }}</p>
    <p v-else class="text-widget__empty">Добавьте текст в настройках виджета</p>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { IWidgetPayload } from 'src/types/Dashboard/dashboard'

const props = defineProps<{
  payload: IWidgetPayload | null
}>()

const content = computed(() => String(props.payload?.data?.content ?? ''))
</script>

<style lang="scss" scoped>
.text-widget {
  &__content {
    margin: 0;
    white-space: pre-wrap;
    color: #282f53;
    line-height: 1.5;
    font-size: 14px;
  }

  &__empty {
    margin: 0;
    color: #9aa0b5;
    font-size: 13px;
  }
}
</style>
