<template>
  <div v-if="payload" class="welcome-widget">
    <div class="welcome-widget__greeting">
      {{ payload.data.greeting }}, {{ payload.data.name }}
    </div>
    <div class="welcome-widget__date">{{ payload.data.date }}</div>
  </div>
</template>

<script setup lang="ts">
import { IWidgetPayload } from 'src/types/Dashboard/dashboard'

defineProps<{
  payload: IWidgetPayload | null
}>()
</script>

<style lang="scss" scoped>
.welcome-widget {
  padding: 8px 4px 4px;

  &__greeting {
    font-size: 22px;
    font-weight: 600;
    color: #282f53;
    line-height: 1.3;
  }

  &__date {
    margin-top: 6px;
    color: #777a8f;
    font-size: 14px;
    text-transform: capitalize;
  }
}
</style>
