<template>
  <article
    class="reminder-card"
    :class="`reminder-card--${urgency}`"
  >
    <div class="reminder-card__main">
      <div class="reminder-card__info">
        <div class="reminder-card__title">
          <q-icon :name="statusIcon" size="18px" />
          <span>{{ statusLabel }}</span>
        </div>
        <div class="reminder-card__when">
          <span class="reminder-card__datetime">{{ humanDatetime(reminder.datetime) }}</span>
          <span class="reminder-card__relative">{{ relativeLabel }}</span>
        </div>
        <div
          v-if="intervalLabel || remindBeforeLabel || lastCompletedLabel"
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
          <span
            v-if="lastCompletedLabel"
            class="reminder-card__last-done"
          >
            {{ lastCompletedLabel }}
          </span>
        </div>
      </div>

      <div class="reminder-card__actions">
        <q-btn
          v-if="canComplete"
          :loading="isCompleting"
          class="reminder-card__complete"
          icon="done"
          label="Выполнено"
          color="grey-8"
          outline
          dense
          no-caps
          @click="completeOccurrence"
        >
          <q-tooltip>{{ completeTooltip }}</q-tooltip>
        </q-btn>

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
          icon="edit"
          size="sm"
          flat
          round
          dense
        >
          <q-tooltip>Редактировать</q-tooltip>
          <q-menu
            ref="editMenuRef"
            @show="hydrateEditModel"
          >
            <div class="q-pa-md">
              <TMReminderForm
                v-model="editModel"
                title="Редактировать напоминание"
                submit-label="Сохранить"
                :loading="isSaving"
                @submit="saveReminder"
              />
            </div>
          </q-menu>
        </q-btn>

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
  </article>
</template>

<script lang="ts" setup>
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { Dialog } from 'quasar'
import { IReminderCreatePayload, IReminderFormModel, IReminderItem } from 'src/types/TaskManager/task'
import { humanDatetime } from 'src/utils/datetime'
import {
  canCompleteReminder,
  formatRemindBefore,
  formatReminderInterval,
  formatReminderRelative,
  getReminderUrgency,
  isRecurringReminder,
  reminderToFormModel
} from 'src/utils/reminder'
import { useTaskStore } from 'src/stores/modules/taskStore'
import TMReminderForm from 'src/components/client/TaskManager/TMReminderForm.vue'

interface IReminderMenuRef {
  hide: () => void
}

const taskStore = useTaskStore()

const props = defineProps<{
  reminder: IReminderItem
  taskId: string
}>()

const now = ref(Date.now())
const isCompleting = ref(false)
const isSaving = ref(false)
const editMenuRef = ref<IReminderMenuRef | null>(null)
const editModel = ref<IReminderFormModel>(reminderToFormModel(props.reminder))
let ticker: ReturnType<typeof setInterval> | null = null

const urgency = computed(() => getReminderUrgency(props.reminder, now.value))
const relativeLabel = computed(() => formatReminderRelative(props.reminder.datetime, now.value))
const intervalLabel = computed(() => formatReminderInterval(props.reminder.interval))
const remindBeforeLabel = computed(() => formatRemindBefore(props.reminder.to_remind_before))
const isRecurring = computed(() => isRecurringReminder(props.reminder))
const canComplete = computed(() => canCompleteReminder(props.reminder))
const completeTooltip = computed(() =>
  isRecurring.value
    ? 'Отметить выполненным — закроет цикл и перенесёт дату'
    : 'Отметить выполненным'
)

const lastCompletedLabel = computed(() => {
  if (!props.reminder.last_completed_at) return null
  return `Последнее: ${humanDatetime(props.reminder.last_completed_at)}`
})

const statusLabel = computed(() => {
  if (urgency.value === 'inactive') return 'Выключено'
  if (props.reminder.awaiting_completion) return 'Нужно подтвердить'
  if (urgency.value === 'overdue') return 'Просрочено'
  if (urgency.value === 'due-soon') return 'Скоро'
  return 'Напоминание'
})

const statusIcon = computed(() => {
  if (urgency.value === 'inactive') return 'notifications_off'
  if (props.reminder.awaiting_completion) return 'notification_important'
  if (urgency.value === 'overdue') return 'notification_important'
  return 'alarm'
})

const chipColor = computed(() => {
  if (urgency.value === 'overdue') return 'negative'
  if (urgency.value === 'due-soon') return 'warning'
  if (urgency.value === 'inactive') return 'grey'
  return 'primary'
})

function hydrateEditModel() {
  editModel.value = reminderToFormModel(props.reminder)
}

async function toggleActive(isActive: boolean) {
  await taskStore.updateReminder(props.taskId, { is_active: isActive })
}

async function saveReminder(payload: IReminderCreatePayload) {
  if (isSaving.value) return

  isSaving.value = true
  try {
    await taskStore.updateReminder(props.taskId, payload)
    editMenuRef.value?.hide()
  } finally {
    isSaving.value = false
  }
}

async function completeOccurrence() {
  if (isCompleting.value || !canComplete.value) return

  isCompleting.value = true
  try {
    await taskStore.completeReminder(props.taskId)
  } finally {
    isCompleting.value = false
  }
}

function confirmDelete() {
  Dialog.create({
    title: 'Удалить напоминание?',
    message: 'Напоминание и история отметок будут удалены.',
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
  padding: 10px 12px;
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

  &__main {
    display: flex;
    align-items: center;
    gap: 10px;
  }

  &__info {
    min-width: 0;
    flex: 1;
  }

  &__title {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
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

  &__when {
    display: flex;
    flex-wrap: wrap;
    align-items: baseline;
    gap: 8px;
    margin-top: 4px;
  }

  &__datetime {
    font-size: 15px;
    font-weight: 650;
    line-height: 1.3;
    color: #1f2439;
  }

  &__relative {
    font-size: 12px;
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
    align-items: center;
    gap: 6px;
    margin-top: 6px;
  }

  &__last-done {
    color: #9ca3af;
    font-size: 12px;
  }

  &__actions {
    display: inline-flex;
    align-items: center;
    gap: 2px;
    flex-shrink: 0;
  }
}
</style>
