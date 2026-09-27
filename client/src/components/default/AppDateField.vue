<template>
  <q-input
    v-model="datetimeModel"
    name="datetime"
    dense
    filled
    @paste="onPaste"
    @blur="onBlur"
  >
    <template v-slot:prepend>
      <q-icon name="event" class="cursor-pointer">
        <q-popup-proxy transition-show="scale" transition-hide="scale" cover>
          <q-date v-model="datetimeModel" mask="YYYY-MM-DD" name="datetime">
            <div class="row items-center justify-end">
              <q-btn label="Close" color="primary" flat v-close-popup />
            </div>
          </q-date>
        </q-popup-proxy>
      </q-icon>
    </template>
  </q-input>
</template>

<script lang="ts" setup>
import { normalizeDateInput } from 'src/utils/datetime'

const datetimeModel = defineModel<string>({ required: true })

function onPaste (event: ClipboardEvent) {
  const text = event.clipboardData?.getData('text') ?? ''
  if (!text.trim()) {
    return
  }

  const normalized = normalizeDateInput(text)
  if (!normalized) {
    return
  }

  event.preventDefault()
  datetimeModel.value = normalized
}

function onBlur () {
  const normalized = normalizeDateInput(datetimeModel.value)
  if (normalized && normalized !== datetimeModel.value) {
    datetimeModel.value = normalized
  }
}
</script>

<style lang="scss" scoped>

</style>
