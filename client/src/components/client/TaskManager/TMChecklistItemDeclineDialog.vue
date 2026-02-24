<template>
  <q-dialog ref="refDialog" v-model="dialogModel" persistent>
    <q-card style="min-width: 350px">
      <q-card-section>
        <div class="text-h6">Decline Reason</div>
      </q-card-section>

      <q-card-section class="q-pt-none">
        <q-input
          ref="refInput"
          v-model="declineReason"
          @keyup.enter="submitDecline"
          name="decline-reason"
          :rules="[
            (val: string) => !!val || 'Field must not be empty',
            (val: string) => val.length >= 5 || '5 symbols min'
          ]"
          dense
          autofocus
        />
      </q-card-section>

      <q-card-actions align="right" class="text-primary">
        <q-btn flat label="Cancel" @click="cancelDecline" />
        <q-btn flat label="Add reason" @click="submitDecline" />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'

interface IInputRef {
  hasError: boolean
  validate: () => boolean | Promise<boolean>
  resetValidation: () => void
}

interface IDialogRef {
  hide: () => void
  show: () => void
}

// Исправляем defineModel - используем стандартный подход
const props = defineProps<{
  modelValue?: boolean | null
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: boolean): void
  (e: 'declined', value: string): void
}>()

// Используем старое связывание т.к. defineModel не принимается по типам в q-dialog
const dialogModel = computed({
  get: () => props.modelValue ?? false,
  set: (value: boolean) => emit('update:modelValue', value)
})

const declineReason = ref('')
const refDialog = ref<IDialogRef | null>(null)
const refInput = ref<IInputRef | null>(null)

const submitDecline = async () => {
  if (refInput.value) {
    const isValid = await refInput.value.validate()

    if (!refInput.value.hasError && isValid) {
      emit('declined', declineReason.value)
      hideDialog()
      clearModel()
    }
  }
}

const cancelDecline = () => {
  hideDialog()
  clearModel()
}

const clearModel = () => {
  if (refInput.value) {
    refInput.value.resetValidation()
    declineReason.value = ''
  }
}

const hideDialog = () => {
  dialogModel.value = false

  if (refDialog.value) {
    refDialog.value.hide()
  }
}
</script>

<style scoped lang="scss">
</style>
