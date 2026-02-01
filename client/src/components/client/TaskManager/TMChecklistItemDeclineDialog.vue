<template>
  <q-dialog ref="refDialog" v-model="prompt" persistent>
    <q-card style="min-width: 350px">
      <q-card-section>
        <div class="text-h6">Decline Reason</div>
      </q-card-section>

      <q-card-section class="q-pt-none">
        <q-input
          ref="refInput"
          v-model="declineReason"
          @keyup.enter="prompt = false"
          name="decline-reason"
          :rules="[
            (val: any) => !!val || 'Field must not be empty',
            (val: any) => val.length >= 5 || '5 symbols min'
          ]"
          dense
          autofocus
        />
      </q-card-section>

      <q-card-actions align="right" class="text-primary">
        <q-btn flat label="Cancel" @click="cancelDecline" />
        <q-btn flat label="Add reason" @click="decline" />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup lang="ts">
import { ref } from 'vue'

const prompt = defineModel(false)
const declineReason = ref('')
const refDialog = ref(null)
const refInput = ref(null)

const emit = defineEmits<{
  (e: 'declined', value: string): void
}>()

const decline = () => {
  refInput?.value.validate()

  if (!refInput?.value.hasError) {
    emit('declined', declineReason.value)
    hideDialog()
    clearModel()
  }
}
const cancelDecline = () => {
  hideDialog()
  clearModel()
}
const clearModel = () => {
  refInput?.value.resetValidation()
  declineReason.value = ''
}
const hideDialog = () => {
  refDialog?.value.hide()
}
</script>

<style scoped lang="scss">

</style>
