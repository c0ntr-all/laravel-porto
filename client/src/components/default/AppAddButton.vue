<template>
  <q-input
    v-if="inputVisible"
    ref="InputRef"
    v-model="inputValue"
    class="input-new-tag d-inline-flex"
    autofocus
    outlined
    dense
    @keyup.enter="handleInputConfirm"
    @blur="handleInputConfirm"
  />
  <q-btn
    v-else
    class="button-new-tag"
    @click="showInput"
    dense
  >
    + New Tag
  </q-btn>
</template>

<script lang="ts" setup>
import { nextTick, ref } from 'vue'
import { QInput } from 'quasar'

const emit = defineEmits<{
  (e: 'created', value: string): void
}>()

const inputValue = ref('')
const inputVisible = ref(false)
const InputRef = ref<InstanceType<typeof QInput>>()

// const handleClose = (tag: string) => {
//
// }

const showInput = () => {
  inputVisible.value = true
  nextTick(() => {
    InputRef.value!.focus()
  })
}

const handleInputConfirm = () => {
  if (inputValue.value) {
    emit('created', inputValue.value)
  }
  inputVisible.value = false
  inputValue.value = ''
}
</script>

<style lang="scss" scoped>
.input-new-tag {
  display: inline-flex;
  width: 100px;
}
</style>
