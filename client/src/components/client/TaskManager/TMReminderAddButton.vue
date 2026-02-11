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
            <AppDatetimeField v-model="reminderModel.datetime" />
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
import { list } from 'radash'
import { useTaskStore } from 'src/stores/modules/taskStore'
import { getCurrentDateTime } from 'src/utils/datetime'
import AppDatetimeField from 'src/components/default/AppDatetimeField.vue'

const taskStore = useTaskStore()

const props = defineProps<{
  taskId: string,
  isReminderAvailable: boolean,
}>()

const minutesOptions = list(1, 60)
const hoursOptions = list(1, 24)
const daysOptions = list(1, 30)
const weeksOptions = list(1, 4)
const monthsOptions = list(1, 12)

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

const defaultInterval = {
  label: 'Не повторять',
  value: null
}

const reminderModel = ref({
  datetime: getCurrentDateTime(),
  interval: defaultInterval,
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

const prepareRequestData = (object: object) => {
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
    interval: defaultInterval,
    is_to_remind_before: false,
    when_to_remind_before_number: ref(1),
    when_to_remind_before_point: ref(whenToRemindBeforePointOptions[0]),
    is_active: true
  }
}

const createReminder = async () => {
  const preparedRequestData = prepareRequestData(reminderModel.value)
  await taskStore.createReminder(props.taskId, preparedRequestData).then(() => {
    clearReminderModel()
  })
}
</script>

<style scoped lang="scss">

</style>
