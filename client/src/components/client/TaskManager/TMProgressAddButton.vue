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
              v-model="progressModel.isFinal"
              label="Final"
            />
            <q-input
              v-model="progressModel.title"
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
            <q-input filled v-model="progressModel.finishedAt">
              <template v-slot:prepend>
                <q-icon name="event" class="cursor-pointer">
                  <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                    <q-date v-model="progressModel.finishedAt" mask="YYYY-MM-DD HH:mm">
                      <div class="row items-center justify-end">
                        <q-btn v-close-popup label="Close" color="primary" flat />
                      </div>
                    </q-date>
                  </q-popup-proxy>
                </q-icon>
              </template>

              <template v-slot:append>
                <q-icon name="access_time" class="cursor-pointer">
                  <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                    <q-time v-model="progressModel.finishedAt" mask="YYYY-MM-DD HH:mm" format24h>
                      <div class="row items-center justify-end">
                        <q-btn v-close-popup label="Close" color="primary" flat />
                      </div>
                    </q-time>
                  </q-popup-proxy>
                </q-icon>
              </template>
            </q-input>

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
import { api } from 'src/boot/axios'
import { AxiosError } from 'axios'
import { handleApiError, handleApiSuccess } from 'src/utils/jsonapi'
import { getCurrentDateTime } from 'src/utils/datetime'
import { ICreateProgressResponse, IProgressItem } from 'src/components/client/TaskManager/types'

const props = defineProps<{
  taskId: number,
  isProgressAvailable: boolean,
}>()

const emit = defineEmits<{
  (e: 'created', value: IProgressItem): void
}>()

const progressModel = {
  title: ref(''),
  content: ref(''),
  isFinal: ref(false),
  finishedAt: ref(getCurrentDateTime())
}
const menuRef = ref()

const createProgress = () => {
  api.post<ICreateProgressResponse>(`v1/task-manager/tasks/${props.taskId}/progress`, {
    title: progressModel.title.value,
    content: progressModel.content.value,
    is_final: progressModel.isFinal.value,
    finished_at: progressModel.finishedAt.value
  }).then((response) => {
    const responseData = response.data.data

    const newProgressItem = {
      id: responseData.id,
      ...responseData.attributes
    }

    emit('created', newProgressItem)

    clearProgressModel()

    if (newProgressItem.is_final) {
      menuRef.value.hide()
    }

    handleApiSuccess(response)
  }).catch((error: AxiosError<{ message: string }>) => {
    handleApiError(error)
  })
}

const clearProgressModel = () => {
  progressModel.title.value = ''
  progressModel.content.value = ''
  progressModel.isFinal.value = false
  progressModel.finishedAt.value = ''
}
</script>

<style scoped lang="scss">

</style>
