<template>
  <div class="reminder-form">
    <div
      v-if="title"
      class="reminder-form__title"
    >
      {{ title }}
    </div>
    <div class="flex column q-gutter-sm">
      <AppDatetimeField v-model="model.datetime" />

      <q-toggle
        v-model="model.is_repeating"
        label="Повторять"
        left-label
      />
      <div
        v-if="model.is_repeating"
        class="row q-col-gutter-sm"
      >
        <div class="col-5">
          <q-input
            v-model.number="model.interval_value"
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
            v-model="model.interval_unit"
            :options="intervalUnitOptions"
            emit-value
            map-options
            filled
            dense
          />
        </div>
      </div>

      <q-toggle
        v-model="model.is_to_remind_before"
        label="Напомнить заранее"
        left-label
      />
      <div
        v-if="model.is_to_remind_before"
        class="row q-col-gutter-sm"
      >
        <div class="col-5">
          <q-input
            v-model.number="model.remind_before_value"
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
            v-model="model.remind_before_unit"
            :options="remindBeforeUnitOptions"
            emit-value
            map-options
            filled
            dense
          />
        </div>
      </div>

      <q-toggle
        v-model="model.is_active"
        label="Активно"
        left-label
      />

      <q-btn
        :loading="loading"
        :label="submitLabel"
        color="primary"
        unelevated
        no-caps
        @click="submit"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import AppDatetimeField from 'src/components/default/AppDatetimeField.vue'
import {
  ReminderBeforeUnitEnum,
  ReminderIntervalUnitEnum
} from 'src/enums/TaskManager/ReminderTimeUnitEnum'
import { IReminderCreatePayload, IReminderFormModel } from 'src/types/TaskManager/task'
import { isReminderPayloadValid, toReminderPayload } from 'src/utils/reminder'

withDefaults(defineProps<{
  title?: string
  submitLabel?: string
  loading?: boolean
}>(), {
  title: '',
  submitLabel: 'Сохранить',
  loading: false
})

const emit = defineEmits<{
  (e: 'submit', payload: IReminderCreatePayload): void
}>()

const model = defineModel<IReminderFormModel>({ required: true })

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

function submit() {
  const payload = toReminderPayload(model.value)
  if (!isReminderPayloadValid(payload)) return
  emit('submit', payload)
}
</script>

<style scoped lang="scss">
.reminder-form {
  width: 280px;

  &__title {
    margin-bottom: 12px;
    font-size: 16px;
    font-weight: 600;
  }
}
</style>
