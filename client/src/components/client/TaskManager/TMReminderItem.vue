<template>
  <article
    class="reminder-card"
    :class="`reminder-card--${urgency}`"
  >
    <div class="reminder-card__header">
      <div class="reminder-card__title">
        <q-icon :name="statusIcon" size="20px" />
        <span>{{ statusLabel }}</span>
      </div>

      <div class="reminder-card__actions">
        <q-toggle
          :model-value="reminder.is_active"
          dense
          color="primary"
          @update:model-value="toggleActive"
        >
          <q-tooltip>
            {{ reminder.is_active ? 'Выключить' : 'Включить' }}
          </q-tooltip>
        </q-toggle>
        <q-btn
          icon="delete"
          size="sm"
          flat
          round
          dense
          @click="confirmDelete"
        >
          <q-tooltip>Удалить</q-tooltip>
        </q-btn>
      </div>
    </div>

    <div class="reminder-card__datetime">
      {{ humanDatetime(reminder.datetime) }}
    </div>
    <div class="reminder-card__relative">
      {{ relativeLabel }}
    </div>

    <div
      v-if="intervalLabel || remindBeforeLabel"
      class="reminder-card__meta"
    >
      <q-chip
        v-if="intervalLabel"
        dense
        outline
        size="sm"
        icon="repeat"
        :color="chipColor"
      >
        {{ intervalLabel }}
      </q-chip>
      <q-chip
        v-if="remindBeforeLabel"
        dense
        outline
        size="sm"
        icon="notifications_active"
        :color="chipColor"
      >
        {{ remindBeforeLabel }}
      </q-chip>
    </div>
  </article>
</template>

<script lang="ts" setup>
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { Dialog } from 'quasar'
import { IReminderItem } from 'src/types/TaskManager/task'
import { humanDatetime } from 'src/utils/datetime'
import {
  formatRemindBefore,
  formatReminderInterval,
  formatReminderRelative,
  getReminderUrgency
} from 'src/utils/reminder'
import { useTaskStore } from 'src/stores/modules/taskStore'

const taskStore = useTaskStore()

const props = defineProps<{
  reminder: IReminderItem
  taskId: string
}>()

const now = ref(Date.now())
let ticker: ReturnType<typeof setInterval> | null = null

const urgency = computed(() => getReminderUrgency(props.reminder, now.value))
const relativeLabel = computed(() => formatReminderRelative(props.reminder.datetime, now.value))
const intervalLabel = computed(() => formatReminderInterval(props.reminder.interval))
const remindBeforeLabel = computed(() => formatRemindBefore(props.reminder.to_remind_before))

const statusLabel = computed(() => {
  if (urgency.value === 'inactive') return 'Напоминание выключено'
  if (urgency.value === 'overdue') return 'Просрочено'
  if (urgency.value === 'due-soon') return 'Скоро'
  return 'Напоминание'
})

const statusIcon = computed(() => {
  if (urgency.value === 'inactive') return 'notifications_off'
  if (urgency.value === 'overdue') return 'notification_important'
  return 'alarm'
})

const chipColor = computed(() => {
  if (urgency.value === 'overdue') return 'negative'
  if (urgency.value === 'due-soon') return 'warning'
  if (urgency.value === 'inactive') return 'grey'
  return 'primary'
})

async function toggleActive(isActive: boolean) {
  await taskStore.updateReminder(props.taskId, { is_active: isActive })
}

function confirmDelete() {
  Dialog.create({
    title: 'Удалить напоминание?',
    message: 'Напоминание будет удалено. Его можно будет создать заново.',
    cancel: { label: 'Отмена', flat: true },
    ok: { label: 'Удалить', color: 'negative' },
    persistent: true
  }).onOk(() => {
    taskStore.deleteReminder(props.taskId)
  })
}

onMounted(() => {
  ticker = setInterval(() => {
    now.value = Date.now()
  }, 30000)
})

onUnmounted(() => {
  if (ticker) clearInterval(ticker)
})
</script>

<style lang="scss" scoped>
.reminder-card {
  padding: 12px 14px;
  border: 1px solid #e4e6ee;
  border-left-width: 3px;
  border-radius: 10px;
  background: #fff;

  &--upcoming {
    border-left-color: $primary;
  }

  &--due-soon {
    background: #fff8e8;
    border-color: #f3d48a;
    border-left-color: $warning;
  }

  &--overdue {
    background: #fdecea;
    border-color: #f3c1c0;
    border-left-color: $negative;
  }

  &--inactive {
    background: #f5f5f7;
    border-left-color: #9e9e9e;
    opacity: 0.92;
  }

  &__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
  }

  &__title {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    font-weight: 600;
    line-height: 1.2;
  }

  &--overdue &__title {
    color: $negative;
  }

  &--due-soon &__title {
    color: #b76a00;
  }

  &--inactive &__title {
    color: #6b7280;
  }

  &__actions {
    display: inline-flex;
    align-items: center;
    gap: 2px;
    flex-shrink: 0;
  }

  &__datetime {
    margin-top: 8px;
    font-size: 18px;
    font-weight: 600;
    line-height: 1.3;
    color: #1f2439;
  }

  &__relative {
    margin-top: 2px;
    font-size: 13px;
    color: #6b7280;
  }

  &--overdue &__relative {
    color: $negative;
    font-weight: 600;
  }

  &--due-soon &__relative {
    color: #b76a00;
    font-weight: 600;
  }

  &__meta {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-top: 10px;
  }
}
</style>
