<template>
  <q-btn
    :disable="!isProgressAvailable"
    label="Add progress"
    color="secondary"
    dense
    unelevated
  >
    <q-menu ref="newProgressMenuRef">
      <div class="row no-wrap q-pa-md">
        <div style="width: 250px">
          <div class="text-h6 q-mb-md">Adding progress</div>
          <div class="flex column q-gutter-sm">
            <q-checkbox
              v-model="progressModel.is_final"
              label="Final"
            />
            <q-input
              v-model="progressModel.title"
              ref="progressTitleRef"
              :rules="[
                (val: any) => !!val || 'Field must not be empty',
                (val: any) => val.length >= 1 || '1 symbol min'
              ]"
              type="text"
              label="Progress title"
              outlined
              dense
            />
            <q-input
              v-model="progressModel.content"
              type="textarea"
              label="Progress content"
              outlined
              dense
            />
            <AppDatetimeField v-model="progressModel.finished_at" />

            <q-btn
              @click="createProgress"
              class="q-mb-xs"
              label="Add"
              color="primary"
              unelevated
            />
          </div>
        </div>
      </div>
    </q-menu>
  </q-btn>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useTaskStore } from 'src/stores/modules/taskStore'
import { getCurrentDateTime } from 'src/utils/datetime'
import { IProgress } from 'src/types/TaskManager/task'
import AppDatetimeField from 'src/components/default/AppDatetimeField.vue'

interface IProgressTitleRef {
  hasError: boolean
  validate: () => void
  resetValidation: () => void
}

interface IProgressMenuRef {
  hide: () => void
}

const taskStore = useTaskStore()

const props = defineProps<{
  taskId: string,
  isProgressAvailable: boolean,
}>()

const progressModel = ref({
  title: '',
  content: '',
  is_final: false,
  finished_at: getCurrentDateTime()
})
const newProgressMenuRef = ref<IProgressMenuRef | null>(null)
const progressTitleRef = ref<IProgressTitleRef | null>(null)

const createProgress = async () => {
  if (progressTitleRef.value) {
    progressTitleRef.value.validate()

    if (!progressTitleRef.value.hasError) {
      await taskStore.createProgress(props.taskId, {
        title: progressModel.value.title,
        content: progressModel.value.content,
        is_final: progressModel.value.is_final,
        finished_at: progressModel.value.finished_at
      }).then((newProgressItem: IProgress | undefined) => {
        if (newProgressItem) {
          clearProgressModel()
          resetValidation()

          if (newProgressMenuRef.value && newProgressItem.is_final) {
            newProgressMenuRef.value.hide()
          }
        }
      })
    }
  }
}

const clearProgressModel = () => {
  progressModel.value.title = ''
  progressModel.value.content = ''
  progressModel.value.is_final = false
  progressModel.value.finished_at = getCurrentDateTime()
}

const resetValidation = () => {
  if (progressTitleRef.value) {
    progressTitleRef.value.resetValidation()
  }
}
</script>

<style scoped lang="scss">

</style>
