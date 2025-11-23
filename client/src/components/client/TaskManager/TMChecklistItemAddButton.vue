<template>
  <q-btn
    v-if="!isActive"
    @click="openForm"
    color="grey"
    icon="add"
    label="Add Item"
    unelevated
    dense
  />
  <div v-if="isActive">
    <q-input
      @keyup.enter="handleForm"
      v-model="model"
      ref="formText"
      class="q-mb-sm"
      dense
      outlined
    />
    <q-btn
      @click="handleForm"
      label="Add"
      color="primary"
      class="q-px-sm q-mr-sm"
      no-caps
      dense
    />
    <q-btn
      @click="closeForm"
      label="Cancel"
      color="danger"
      size="md"
      class="q-px-sm"
      no-caps
      flat
      dense
    />
  </div>
</template>

<script lang="ts" setup>
import { computed, inject, nextTick, ref } from 'vue'

const props = defineProps<{
  checklistId: string
}>()
const emit = defineEmits<{
  (e: 'processed', value: any): void
}>()

const formText = ref<HTMLElement | null>(null)
const model = ref<string | null>(null)
const activeFormId = inject('activeFormId', ref<string | null>(null))
const setActiveChecklistForm = inject<{(id: string | null): void}>('setActiveChecklistForm')!
const isActive = computed(() => activeFormId.value === props.checklistId)

const openForm = () => {
  setActiveChecklistForm(props.checklistId)

  nextTick(() => {
    formText.value?.focus()
  })
}
const closeForm = () => {
  if (activeFormId.value === props.checklistId) {
    setActiveChecklistForm(null)
  }
}

const handleForm = () => {
  emit('processed', model)
}

defineExpose({
  clearModel: () => {
    model.value = null
  }
})
</script>

<style scoped>

</style>
