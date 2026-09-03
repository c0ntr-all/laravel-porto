<template>
  <article
    :id="`notification-${notification.id}`"
    class="notification-item"
    :class="{
      'notification-item--unread': !notification.is_read,
      'notification-item--flash': flashing,
      'notification-item--compact': compact
    }"
    :data-notification-id="notification.id"
  >
    <div
      class="notification-item__main"
      role="link"
      tabindex="0"
      @click="onSelect"
      @keydown.enter.prevent="onSelect"
    >
      <div class="notification-item__icon" :class="iconClass">
        <q-icon :name="iconName" />
      </div>
      <div class="notification-item__content">
        <div class="notification-item__header">
          <div class="notification-item__title">{{ notification.title }}</div>
          <div class="notification-item__time">{{ createdLabel }}</div>
        </div>
        <NotificationBody :body="notification.body" :preview-length="previewLength" />
      </div>
    </div>
  </article>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { date } from 'quasar'
import { INotification } from 'src/types/notification'
import NotificationBody from 'src/components/client/Notifications/NotificationBody.vue'

const props = withDefaults(defineProps<{
  notification: INotification
  flashing?: boolean
  compact?: boolean
  previewLength?: number
}>(), {
  flashing: false,
  compact: false
})

const emit = defineEmits<{
  select: [id: string]
}>()

const iconName = computed(() => {
  return props.notification.type === 'reminder.due' ? 'alarm' : 'notifications'
})

const iconClass = computed(() => {
  return props.notification.type === 'reminder.due'
    ? 'notification-item__icon--reminder'
    : 'notification-item__icon--system'
})

const createdLabel = computed(() => {
  if (!props.notification.created_at) {
    return ''
  }

  return date.formatDate(props.notification.created_at, 'D MMM, HH:mm')
})

const onSelect = (): void => {
  emit('select', props.notification.id)
}
</script>

<style lang="scss" scoped>
.notification-item {
  border-radius: 14px;
  background: #fff;
  transition: background-color 0.4s ease;

  &--unread {
    background: rgba(108, 95, 252, 0.06);
  }

  &--flash {
    animation: notification-flash 1.4s ease;
  }

  &__main {
    display: flex;
    gap: 12px;
    width: 100%;
    padding: 12px;
    border: 0;
    background: transparent;
    text-align: left;
    cursor: pointer;
  }

  &:hover {
    background: rgba(108, 95, 252, 0.05);
  }

  &--compact &__main {
    padding: 10px;
  }

  &__icon {
    display: flex;
    flex-shrink: 0;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: 12px;
    color: $primary;
    background: $primary-light;

    &--reminder {
      color: #c4451a;
      background: rgba(242, 192, 55, 0.22);
    }
  }

  &__content {
    min-width: 0;
    flex: 1;
  }

  &__header {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    gap: 8px;
    margin-bottom: 4px;
  }

  &__title {
    font-size: 14px;
    font-weight: 600;
    color: #282f53;
    line-height: 1.3;
  }

  &__time {
    flex-shrink: 0;
    font-size: 11px;
    color: #8b8ea3;
  }
}

@keyframes notification-flash {
  0%,
  100% {
    background-color: #fff;
  }

  18%,
  55% {
    background-color: rgba(108, 95, 252, 0.22);
  }
}
</style>
