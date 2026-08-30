<template>
  <q-btn
    :disable="!isReminderAvailable"
    label="Add reminder"
    color="secondary"
    dense
    unelevated
  >
    <q-menu ref="newReminderMenuRef">
      <div class="row no-wrap q-pa-md">
        <div class="reminder-form">
          <div class="text-h6 q-mb-md">Adding reminder</div>
          <div class="flex column q-gutter-sm">
            <AppDatetimeField v-model="reminderModel.datetime" />

            <q-toggle
              v-model="reminderModel.is_repeating"
              label="Повторять"
              left-label
            />
            <div
              v-if="reminderModel.is_repeating"
              class="row q-col-gutter-sm"
            >
              <div class="col-5">
                <q-input
                  v-model.number="reminderModel.interval_value"
                  type="number"
                  min="1"
                  max="999"
                  label="Каждые"
                  filled
                  dense
                />
              </div>
              <div class="col-7">
                <q-select
                  v-model="reminderModel.interval_unit"
                  :options="intervalUnitOptions"
                  emit-value
                  map-options
                  filled
                  dense
                />
              </div>
            </div>

            <q-toggle
              v-model="reminderModel.is_to_remind_before"
              label="Напомнить заранее"
              left-label
            />
            <div
              v-if="reminderModel.is_to_remind_before"
              class="row q-col-gutter-sm"
            >
              <div class="col-5">
                <q-input
                  v-model.number="reminderModel.remind_before_value"
                  type="number"
                  min="1"
                  max="9999"
                  label="За"
                  filled
                  dense
                />
              </div>
              <div class="col-7">
                <q-select
                  v-model="reminderModel.remind_before_unit"
                  :options="remindBeforeUnitOptions"
                  emit-value
                  map-options
                  filled
                  dense
                />
              </div>
            </div>

            <q-toggle
              v-model="reminderModel.is_active"
              label="Active"
              left-label
            />

            <q-btn
              :loading="isSubmitting"
              class="q-mb-xs"
              label="Add"
              color="primary"
              unelevated
              @click="createReminder"
            />
          </div>
        </div>
      </div>
    </q-menu>
  </q-btn>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useTaskStore } from 'src/stores/modules/taskStore'
import { getCurrentDateTime } from 'src/utils/datetime'
import { toReminderDatetime } from 'src/utils/reminder'
import AppDatetimeField from 'src/components/default/AppDatetimeField.vue'
import {
  ReminderBeforeUnitEnum,
  ReminderIntervalUnitEnum
} from 'src/enums/TaskManager/ReminderTimeUnitEnum'
import {
  IReminderCreatePayload,
  ReminderBeforeUnit,
  ReminderIntervalUnit
} from 'src/types/TaskManager/task'

interface IReminderMenuRef {
  hide: () => void
}

interface IReminderFormModel {
  datetime: string
  is_repeating: boolean
  interval_value: number
  interval_unit: ReminderIntervalUnit
  is_to_remind_before: boolean
  remind_before_value: number
  remind_before_unit: ReminderBeforeUnit
  is_active: boolean
}

const taskStore = useTaskStore()

const props = defineProps<{
  taskId: string,
  isReminderAvailable: boolean,
}>()

const intervalUnitOptions = [
  { label: 'час', value: ReminderIntervalUnitEnum.HOUR },
  { label: 'день', value: ReminderIntervalUnitEnum.DAY },
  { label: 'неделя', value: ReminderIntervalUnitEnum.WEEK },
  { label: 'месяц', value: ReminderIntervalUnitEnum.MONTH },
  { label: 'год', value: ReminderIntervalUnitEnum.YEAR }
]

const remindBeforeUnitOptions = [
  { label: 'мин.', value: ReminderBeforeUnitEnum.MINUTE },
  { label: 'час', value: ReminderBeforeUnitEnum.HOUR },
  { label: 'день', value: ReminderBeforeUnitEnum.DAY },
  { label: 'неделя', value: ReminderBeforeUnitEnum.WEEK }
]

const newReminderMenuRef = ref<IReminderMenuRef | null>(null)
const isSubmitting = ref(false)
const reminderModel = ref<IReminderFormModel>(createDefaultModel())

function createDefaultModel(): IReminderFormModel {
  return {
    datetime: getCurrentDateTime(),
    is_repeating: false,
    interval_value: 1,
    interval_unit: ReminderIntervalUnitEnum.DAY,
    is_to_remind_before: false,
    remind_before_value: 30,
    remind_before_unit: ReminderBeforeUnitEnum.MINUTE,
    is_active: true
  }
}

function toPayload(model: IReminderFormModel): IReminderCreatePayload {
  const payload: IReminderCreatePayload = {
    datetime: toReminderDatetime(model.datetime),
    is_active: model.is_active
  }

  if (model.is_repeating) {
    payload.interval = {
      value: Number(model.interval_value),
      unit: model.interval_unit
    }
  }

  if (model.is_to_remind_before) {
    payload.to_remind_before = {
      value: Number(model.remind_before_value),
      unit: model.remind_before_unit
    }
  }

  return payload
}

function isPayloadValid(payload: IReminderCreatePayload): boolean {
  if (!payload.datetime) return false
  if (payload.interval && (!payload.interval.value || payload.interval.value < 1)) return false
  if (payload.to_remind_before && (!payload.to_remind_before.value || payload.to_remind_before.value < 1)) {
    return false
  }

  return true
}

const createReminder = async () => {
  const payload = toPayload(reminderModel.value)
  if (!isPayloadValid(payload) || isSubmitting.value) return

  isSubmitting.value = true
  try {
    const created = await taskStore.createReminder(props.taskId, payload)
    if (created) {
      reminderModel.value = createDefaultModel()
      newReminderMenuRef.value?.hide()
    }
  } finally {
    isSubmitting.value = false
  }
}
</script>

<style scoped lang="scss">
.reminder-form {
  width: 280px;
}
</style>
