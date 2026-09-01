<template>
  <q-dialog :model-value="modelValue" @update:model-value="emit('update:modelValue', $event)">
    <q-card style="min-width: 380px">
      <q-card-section class="row items-center">
        <div class="text-h6">Новый дашборд</div>
        <q-space />
        <q-btn v-close-popup icon="close" flat round dense />
      </q-card-section>

      <q-card-section class="q-gutter-md">
        <q-input v-model="name" outlined dense label="Название" />
        <q-input v-model="description" outlined dense label="Описание" />
        <q-toggle v-model="withDefaults" color="primary" label="Добавить виджеты по умолчанию" />
      </q-card-section>

      <q-card-actions align="right">
        <q-btn v-close-popup flat label="Отмена" />
        <q-btn color="primary" unelevated label="Создать" :disable="!name.trim()" @click="submit" />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import { IDashboardCreatePayload } from 'src/types/Dashboard/dashboard'

const props = defineProps<{
  modelValue: boolean
}>()

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
  create: [payload: IDashboardCreatePayload]
}>()

const name = ref('')
const description = ref('')
const withDefaults = ref(false)

watch(() => props.modelValue, (open) => {
  if (open) {
    name.value = ''
    description.value = ''
    withDefaults.value = false
  }
})

function submit(): void {
  emit('create', {
    name: name.value.trim(),
    description: description.value.trim() || null,
    with_defaults: withDefaults.value
  })
  emit('update:modelValue', false)
}
</script>
