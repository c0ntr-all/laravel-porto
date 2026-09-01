<template>
  <div class="html-widget">
    <div v-if="html" class="html-widget__body" v-html="html" />
    <p v-else class="html-widget__empty">Добавьте HTML в настройках виджета</p>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { IWidgetPayload } from 'src/types/Dashboard/dashboard'

const props = defineProps<{
  payload: IWidgetPayload | null
}>()

const html = computed(() => props.payload?.html || String(props.payload?.data?.html ?? ''))
</script>

<style lang="scss" scoped>
.html-widget {
  font-size: 14px;
  color: #282f53;
  line-height: 1.5;

  &__empty {
    margin: 0;
    color: #9aa0b5;
    font-size: 13px;
  }
}
</style>
