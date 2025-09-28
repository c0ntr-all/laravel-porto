<template>
  <div v-if="content" v-html="content" class="tm-textarea"></div>
  <div v-else class="tm-textarea text-grey-5">There is no description!</div>
  <q-popup-edit
    ref="contentEditorRef"
    v-model="content"
    auto-save
    v-slot="scope"
  >
    <q-editor
      v-model="scope.value"
      min-height="5rem"
      autofocus
      @keyup.enter.stop
      counter
    />
    <div class="q-pt-sm">
      <q-btn @click="updateContent(scope.value)" label="Save" color="primary" flat/>
      <q-btn @click="cancelUpdate" label="Cancel" color="primary" flat/>
    </div>
  </q-popup-edit>
</template>

<script setup lang="ts">
import { ref } from 'vue'

const emit = defineEmits<{
  (e: 'updated', value: string): void
}>()

const content = defineModel<string | undefined>('content', {
  required: true
})

const contentEditorRef = ref<InstanceType<typeof import('quasar').QPopupEdit> | null>(null)

defineExpose({
  onUpdateSuccess: () => {
    if (contentEditorRef.value) {
      contentEditorRef.value.set()
    }
  },
  onUpdateError: () => {
    cancelUpdate()
  }
})

const updateContent = (newContent: string) => {
  emit('updated', newContent)
}
const cancelUpdate = () => {
  if (contentEditorRef.value) {
    contentEditorRef?.value.cancel()
  }
}
</script>

<style scoped lang="scss">
.tm-textarea {
  &:hover {
    cursor: pointer;
    background: #f4f5f7;
  }
}
</style>
