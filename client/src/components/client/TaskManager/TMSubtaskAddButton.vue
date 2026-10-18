<template>
  <q-btn
    label="Add subtask"
    color="secondary"
    dense
    unelevated
  >
    <q-menu ref="menuRef">
      <div class="row no-wrap q-pa-md">
        <div class="subtask-add">
          <div class="text-h6 q-mb-md">Новая подзадача</div>
          <div class="flex column q-gutter-sm">
            <q-input
              v-model="title"
              ref="titleRef"
              :rules="[
                (val: string) => !!val?.trim() || 'Field must not be empty'
              ]"
              label="Название задачи"
              outlined
              dense
              autofocus
              @keyup.enter="createSubtask"
            />

            <div class="row q-gutter-sm justify-end">
              <q-btn
                label="Отмена"
                color="primary"
                flat
                dense
                @click="cancel"
              />
              <q-btn
                label="Создать"
                color="primary"
                unelevated
                dense
                :loading="isSubmitting"
                :disable="!title.trim()"
                @click="createSubtask"
              />
            </div>
          </div>
        </div>
      </div>
    </q-menu>
  </q-btn>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useTaskStore } from 'src/stores/modules/taskStore'

interface ITitleRef {
  hasError: boolean
  validate: () => boolean
  resetValidation: () => void
}

interface IMenuRef {
  hide: () => void
}

const taskStore = useTaskStore()

const props = defineProps<{
  taskId: string
}>()

const title = ref('')
const isSubmitting = ref(false)
const menuRef = ref<IMenuRef | null>(null)
const titleRef = ref<ITitleRef | null>(null)

const resetForm = () => {
  title.value = ''
  titleRef.value?.resetValidation()
}

const cancel = () => {
  resetForm()
  menuRef.value?.hide()
}

const createSubtask = async () => {
  if (isSubmitting.value) return

  titleRef.value?.validate()
  if (titleRef.value?.hasError || !title.value.trim()) return

  isSubmitting.value = true
  try {
    const created = await taskStore.createTask({
      title: title.value.trim(),
      parent_id: props.taskId
    })

    if (created) {
      resetForm()
      menuRef.value?.hide()
    }
  } finally {
    isSubmitting.value = false
  }
}
</script>

<style scoped lang="scss">
.subtask-add {
  width: 250px;
}
</style>
