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
        <div style="width: 250px">
          <div class="text-h6 q-mb-md">Adding reminder</div>
          <div class="flex column q-gutter-sm">
            <q-input dense filled v-model="reminderModel.datetime">
              <template v-slot:prepend>
                <q-icon name="event" class="cursor-pointer">
                  <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                    <q-date v-model="reminderModel.datetime" mask="YYYY-MM-DD HH:mm">
                      <div class="row items-center justify-end">
                        <q-btn v-close-popup label="Close" color="primary" flat />
                      </div>
                    </q-date>
                  </q-popup-proxy>
                </q-icon>
              </template>

              <template v-slot:append>
                <q-icon name="access_time" class="cursor-pointer">
                  <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                    <q-time v-model="reminderModel.datetime" mask="YYYY-MM-DD HH:mm" format24h>
                      <div class="row items-center justify-end">
                        <q-btn v-close-popup label="Close" color="primary" flat />
                      </div>
                    </q-time>
                  </q-popup-proxy>
                </q-icon>
              </template>
            </q-input>
            <q-select
              v-model="reminderModel.interval"
              :options="reminderIntervals"
              label="Интервал"
              :options-html="true"
              filled
              dense
            />
            <q-toggle
              v-model="reminderModel.is_to_remind_before"
              checked-icon="add"
              unchecked-icon="remove"
              label="Напомнить"
              left-label
            />
            <template v-if="reminderModel.is_to_remind_before">
              <div class="row q-col-gutter-sm">
                <div class="col-6">
                  <q-select
                    v-model="reminderModel.when_to_remind_before_number"
                    :options="whenToRemindBeforeNumberOptions"
                    :options-html="true"
                    filled
                    dense
                  />
                </div>
                <div class="col-6">
                  <q-select
                    v-model="reminderModel.when_to_remind_before_point"
                    :options="whenToRemindBeforePointOptions"
                    :options-html="true"
                    filled
                    dense
                  />
                </div>
              </div>
            </template>
            <q-toggle
              v-model="reminderModel.is_active"
              checked-icon="add"
              unchecked-icon="remove"
              label="Active"
              left-label
            />

            <q-btn
              @click="createReminder"
              class="q-mb-xs"
              label="Add"
              color="primary"
              unelevated
            />
          </div>
        </div>
      </div>
    </q-menu>
  </q-btn>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { getCurrentDateTime } from 'src/utils/datetime'
import { ICreateReminderResponse, IReminderItem } from 'src/components/client/TaskManager/types'
import { api } from 'src/boot/axios'
import { handleApiError, handleApiSuccess } from 'src/utils/jsonapi'
import { AxiosError } from 'axios'

const props = defineProps<{
  taskId: number,
  isReminderAvailable: boolean,
}>()

const emit = defineEmits<{
  (e: 'created', value: IReminderItem): void
}>()

function range(start, end) {
  const length = Math.abs(end - start) + 1
  const direction = start < end ? 1 : -1

  return Array.from({ length }, (_, index) => start + index * direction)
}
const minutesOptions = range(1, 60)
const hoursOptions = range(1, 60)
const daysOptions = range(1, 30)
const weeksOptions = range(1, 4)
const monthsOptions = range(1, 12)

const whenToRemindBeforePointOptions = [{
  label: 'мин.',
  value: 'minutes'
}, {
  label: 'ч.',
  value: 'hours'
}, {
  label: 'дн.',
  value: 'days'
}, {
  label: 'нед.',
  value: 'weeks'
}, {
  label: 'мес.',
  value: 'months'
}]
const whenToRemindBeforeNumberOptions = computed(() => {
  const point = reminderModel.value.when_to_remind_before_point.value

  switch (point) {
    case 'minutes':
      return minutesOptions
    case 'hours':
      return hoursOptions
    case 'days':
      return daysOptions
    case 'weeks':
      return weeksOptions
    case 'months':
      return monthsOptions
    default:
      return []
  }
})

const reminderModel = ref({
  datetime: getCurrentDateTime(),
  interval: {
    label: 'Не повторять',
    value: null
  },
  is_to_remind_before: false,
  when_to_remind_before_number: ref(1),
  when_to_remind_before_point: ref(whenToRemindBeforePointOptions[0]),
  is_active: true
})
const reminderIntervals = [{
  label: 'Не повторять',
  value: null
}, {
  label: '1 час',
  value: '1 hour'
}, {
  label: '1 день',
  value: '1 day'
}, {
  label: '1 неделя',
  value: '1 week'
}, {
  label: '1 месяц',
  value: '1 month'
}, {
  label: '1 год',
  value: '1 year'
}]

const prepareRequestData = (object) => {
  const fieldsToRemove = ['is_to_remind_before']
  const labelValueFields = ['interval']

  const result = JSON.parse(JSON.stringify(object))

  if (result.is_to_remind_before) {
    const number = result.when_to_remind_before_number?.value || result.when_to_remind_before_number
    const point = result.when_to_remind_before_point?.value || result.when_to_remind_before_point

    result.remind_before = `${number} ${point}`

    delete result.when_to_remind_before_number
    delete result.when_to_remind_before_point
  }

  fieldsToRemove.forEach(field => delete result[field])

  labelValueFields.forEach(field => {
    if (result[field] && typeof result[field] === 'object') {
      result[field] = result[field].value
    }
  })

  return result
}

const clearReminderModel = () => {
  reminderModel.value = {
    datetime: getCurrentDateTime(),
    interval: null,
    to_remind_before: null,
    is_active: true,
    is_regular: false
  }
}

const createReminder = () => {
  const preparedRequestData = prepareRequestData(reminderModel.value)
  api.post<ICreateReminderResponse>(`v1/task-manager/tasks/${props.taskId}/reminder`, {
    ...preparedRequestData
  }).then((response) => {
    const responseData = response.data.data
    const { id, attributes } = responseData

    const newReminder = {
      id,
      ...attributes
    }

    emit('created', newReminder)

    clearReminderModel()
    handleApiSuccess(response)
  }).catch((error: AxiosError<{ message: string }>) => {
    handleApiError(error)
  })
}
</script>

<style scoped lang="scss">

</style>
