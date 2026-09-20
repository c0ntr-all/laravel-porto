<template>
  <q-btn
    :disable="!isReminderAvailable"
    label="Add reminder"
    color="secondary"
    dense
    unelevated
  >
    <q-menu ref="newReminderMenuRef">
      <div class="q-pa-md">
        <TMReminderForm
          v-model="reminderModel"
          title="Новое напоминание"
          submit-label="Добавить"
          :loading="isSubmitting"
          @submit="createReminder"
        />
      </div>
    </q-menu>
  </q-btn>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useTaskStore } from 'src/stores/modules/taskStore'
import { IReminderCreatePayload, IReminderFormModel } from 'src/types/TaskManager/task'
import { createReminderFormModel } from 'src/utils/reminder'
import TMReminderForm from 'src/components/client/TaskManager/TMReminderForm.vue'

interface IReminderMenuRef {
  hide: () => void
}

const taskStore = useTaskStore()

const props = defineProps<{
  taskId: string,
  isReminderAvailable: boolean,
}>()

const newReminderMenuRef = ref<IReminderMenuRef | null>(null)
const isSubmitting = ref(false)
const reminderModel = ref<IReminderFormModel>(createReminderFormModel())

const createReminder = async (payload: IReminderCreatePayload) => {
  if (isSubmitting.value) return

  isSubmitting.value = true
  try {
    const created = await taskStore.createReminder(props.taskId, payload)
    if (created) {
      reminderModel.value = createReminderFormModel()
      newReminderMenuRef.value?.hide()
    }
  } finally {
    isSubmitting.value = false
  }
}
</script>
