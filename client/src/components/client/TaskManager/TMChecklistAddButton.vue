<template>
  <q-btn
    label="Add checklist"
    color="secondary"
    dense
    unelevated
  >
    <q-menu>
      <div class="row no-wrap q-pa-md">
        <div style="width: 250px">
          <div class="text-h6 q-mb-md">Adding checklist</div>
          <div class="flex column q-gutter-sm">
            <q-input
              v-model="newChecklistTitle"
              label="Checklist name"
              outlined
              dense
            />

            <q-btn
              @click="createChecklist"
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
import { IChecklist, ICreateChecklistResponse } from 'src/components/client/TaskManager/types'
import { handleApiError, handleApiSuccess } from 'src/utils/jsonapi'
import { AxiosError } from 'axios'

const props = defineProps<{
  taskId: number,
}>()

const emit = defineEmits<{
  (e: 'created', value: IChecklist): void
}>()

const newChecklistTitle = ref('Check list')

const createChecklist = () => {
  api.post<ICreateChecklistResponse>(`v1/task-manager/tasks/${props.taskId}/checklists`, {
    title: newChecklistTitle.value
  }).then((response) => {
    const responseData = response.data.data

    const newChecklist = {
      id: responseData.id,
      title: responseData.attributes.title,
      created_at: responseData.attributes.created_at,
      updated_at: responseData.attributes.updated_at,
      relationships: {
        checklistItems: {
          data: [],
          meta: {
            count: 1
          }
        }
      }
    }

    emit('created', newChecklist)

    handleApiSuccess(response)
  }).catch((error: AxiosError<{ message: string }>) => {
    handleApiError(error)
  })
}
</script>

<style scoped lang="scss">

</style>
