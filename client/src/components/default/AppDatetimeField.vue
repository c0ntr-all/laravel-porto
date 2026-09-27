<template>
  <q-input
    v-model="datetimeModel"
    dense
    filled
    @paste="onPaste"
    @blur="onBlur"
  >
    <template v-slot:prepend>
      <q-icon name="event" class="cursor-pointer">
        <q-popup-proxy transition-show="scale" transition-hide="scale" cover>
          <q-date v-model="datetimeModel" mask="YYYY-MM-DD HH:mm">
            <div class="row items-center justify-end">
              <q-btn label="Close" color="primary" flat v-close-popup />
            </div>
          </q-date>
        </q-popup-proxy>
      </q-icon>
    </template>

    <template v-slot:append>
      <q-icon name="access_time" class="cursor-pointer">
        <q-popup-proxy transition-show="scale" transition-hide="scale" cover>
          <q-time v-model="datetimeModel" mask="YYYY-MM-DD HH:mm" format24h>
            <div class="row items-center justify-end">
              <q-btn label="Close" color="primary" flat v-close-popup />
            </div>
          </q-time>
        </q-popup-proxy>
      </q-icon>
    </template>
  </q-input>
</template>

<script lang="ts" setup>
import { normalizeDatetimeInput } from 'src/utils/datetime'

const datetimeModel = defineModel<string>({ required: true })

function applyNormalized (raw: string): boolean {
  const normalized = normalizeDatetimeInput(raw)
  if (!normalized || normalized === datetimeModel.value) {
    return false
  }

  datetimeModel.value = normalized
  return true
}

function onPaste (event: ClipboardEvent) {
  const text = event.clipboardData?.getData('text') ?? ''
  if (!text.trim()) {
    return
  }

  const normalized = normalizeDatetimeInput(text)
  if (!normalized) {
    return
  }

  event.preventDefault()
  datetimeModel.value = normalized
}

function onBlur () {
  applyNormalized(datetimeModel.value)
}
</script>

<style lang="scss" scoped>

</style>
