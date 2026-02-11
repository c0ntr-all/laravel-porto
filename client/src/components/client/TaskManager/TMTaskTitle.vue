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
        @keyup.enter="saveTitle(scope.value)"
        dense
        autofocus
        counter
        name="title"
      />
      <div class="q-pt-sm">
        <q-btn @click="saveTitle(scope.value)" label="Save" color="primary" flat/>
        <q-btn @click="cancelSave" label="Cancel" color="primary" flat/>
      </div>
    </q-popup-edit>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'

const emit = defineEmits<{
  (e: 'saved', value: string): void
}>()

const title = defineModel<string>('title', {
  required: true
})
const titleEditorRef = ref<InstanceType<typeof import('quasar').QPopupEdit> | null>(null)

defineExpose({
  onSaveSuccess: () => {
    titleEditorRef.value?.set()
  },
  onSaveError: () => {
    titleEditorRef.value?.cancel()
  }
})

const cancelSave = () => {
  if (titleEditorRef.value) {
    titleEditorRef?.value.cancel()
  }
}

const saveTitle = (value: string) => {
  emit('saved', value)
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
