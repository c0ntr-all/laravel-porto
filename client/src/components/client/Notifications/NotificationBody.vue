<template>
  <div class="notification-body">
    <p class="notification-body__text">
      {{ visibleText }}
    </p>
    <button
      v-if="isLong"
      type="button"
      class="notification-body__toggle"
      @click.stop="expanded = !expanded"
    >
      {{ expanded ? 'свернуть' : 'развернуть' }}
    </button>
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { previewNotificationBody } from 'src/api/mappers/notification.mapper'
import { NOTIFICATION_PREVIEW_LENGTH } from 'src/constants/notifications'

const props = withDefaults(defineProps<{
  body: string
  previewLength?: number
}>(), {
  previewLength: NOTIFICATION_PREVIEW_LENGTH
})

const expanded = ref(false)

const isLong = computed(() => props.body.trim().length > props.previewLength)
const visibleText = computed(() => {
  if (!isLong.value || expanded.value) {
    return props.body
  }

  return previewNotificationBody(props.body, props.previewLength)
})
</script>

<style lang="scss" scoped>
.notification-body {
  min-width: 0;

  &__text {
    margin: 0;
    white-space: pre-wrap;
    word-break: break-word;
    color: #5b5f76;
    font-size: 13px;
    line-height: 1.45;
  }

  &__toggle {
    margin-top: 4px;
    padding: 0;
    border: 0;
    background: transparent;
    color: $primary;
    cursor: pointer;
    font-size: 12px;
    font-weight: 600;
    line-height: 1.2;
  }
}
</style>
