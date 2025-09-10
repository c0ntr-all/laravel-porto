<template>
  <div class="tm-text text-h6">
    {{ title }}
    <q-popup-edit
      ref="titleEditorRef"
      v-model="title"
      auto-save
      v-slot="scope"
    >
      <q-input
        v-model="scope.value"
        @keyup.enter="updateTitle(scope.value)"
        dense
        autofocus
        counter
      />
      <div class="q-pt-sm">
        <q-btn @click="updateTitle(scope.value)" label="Save" color="primary" flat/>
        <q-btn @click="titlePopup?.cancel()" label="Cancel" color="primary" flat/>
      </div>
    </q-popup-edit>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'

const emit = defineEmits<{
  (e: 'updated', value: string): void
}>()

const title = defineModel<string>('title', {
  required: true
})
const titleEditorRef = ref<InstanceType<typeof import('quasar').QPopupEdit> | null>(null)

defineExpose({
  onUpdateSuccess: () => {
    titleEditorRef.value?.set()
  },
  onUpdateError: () => {
    titleEditorRef.value?.cancel()
  }
})

const updateTitle = (value: string) => {
  emit('updated', value)
}
</script>

<style scoped lang="scss">
.tm-text {
  width: 100%;

  &:hover {
    cursor: pointer;
    background: #f4f5f7;
  }
}
</style>
