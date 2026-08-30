<template>
  <div
    class="occurrence"
    :class="`occurrence--${occurrence.status}`"
  >
    <q-icon
      class="occurrence__icon"
      :name="icon"
      :color="iconColor"
      size="18px"
    />
    <div class="occurrence__body">
      <div class="occurrence__title">
        {{ humanDatetime(occurrence.scheduled_at) }}
      </div>
      <div class="occurrence__caption">
        {{ caption }}
      </div>
    </div>
    <q-badge
      v-if="occurrence.status === ReminderOccurrenceStatusEnum.COMPLETED && occurrence.is_overdue"
      color="negative"
      outline
    >
      С опозданием
    </q-badge>
    <q-badge
      v-else-if="occurrence.status === ReminderOccurrenceStatusEnum.NOTIFIED"
      color="warning"
      outline
    >
      Ожидает
    </q-badge>
  </div>
</template>

<script lang="ts" setup>
import { computed } from 'vue'
import { IReminderOccurrence } from 'src/types/TaskManager/task'
import { ReminderOccurrenceStatusEnum } from 'src/enums/TaskManager/ReminderTimeUnitEnum'
import { humanDatetime } from 'src/utils/datetime'

const props = defineProps<{
  occurrence: IReminderOccurrence
}>()

const icon = computed(() => {
  if (props.occurrence.status === ReminderOccurrenceStatusEnum.COMPLETED) {
    return props.occurrence.is_overdue ? 'warning' : 'check_circle'
  }
  return 'notifications'
})

const iconColor = computed(() => {
  if (props.occurrence.status === ReminderOccurrenceStatusEnum.COMPLETED) {
    return props.occurrence.is_overdue ? 'negative' : 'positive'
  }
  return 'warning'
})

const caption = computed(() => {
  if (props.occurrence.status === ReminderOccurrenceStatusEnum.COMPLETED && props.occurrence.completed_at) {
    return `Выполнено ${humanDatetime(props.occurrence.completed_at)}`
  }

  if (props.occurrence.notified_at) {
    return `Напомнили ${humanDatetime(props.occurrence.notified_at)}`
  }

  return 'Цикл ещё не закрыт'
})
</script>

<style lang="scss" scoped>
.occurrence {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 0;

  &__icon {
    flex-shrink: 0;
  }

  &__body {
    min-width: 0;
    flex: 1;
  }

  &__title {
    font-size: 13px;
    font-weight: 600;
    line-height: 1.3;
    color: #1f2439;
  }

  &__caption {
    margin-top: 1px;
    font-size: 12px;
    color: #6b7280;
  }
}
</style>
