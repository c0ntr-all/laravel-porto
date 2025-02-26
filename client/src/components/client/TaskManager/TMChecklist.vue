<template>
  <q-card-section>
    <div class="text-h6 q-mb-sm">{{ checklist.title }}</div>
    <template v-if="checklist.relationships.checklistItems.data.length">
      <q-list class="q-mb-md" bordered separator>
        <q-item v-for="checklistItem in checklist?.relationships?.checklistItems?.data" :key="checklistItem.id"
                dense>
          <q-item-section avatar>
            <q-checkbox
              v-model="selectedItems"
              :val="checklistItem.id"
              @click="updateChecklistItem()"
            />
          </q-item-section>
          <q-item-section>{{ checklistItem.title }}</q-item-section>
        </q-item>
      </q-list>
    </template>
    <TMChecklistItemAddButton
      @processed="createChecklistItem"
      :checklist-id="checklist.id"
    />
  </q-card-section>
</template>

<script lang="ts" setup>
import { provide, ref } from 'vue'
import { api } from 'src/boot/axios'
import { handleApiError, handleApiSuccess } from 'src/utils/jsonapi'
import { AxiosError } from 'axios'
import TMChecklistItemAddButton from 'src/components/client/TaskManager/TMChecklistItemAddButton.vue'
import { IChecklist, ITask } from 'src/components/client/TaskManager/types'

interface ICreateChecklistItemResponse {
  data: {
    type: string
    id: string
    attributes: {
      title: string
      finished_at: string
      created_at: string
    }
  },
  meta: {
    message?: string
  }
}

const props = defineProps<{
  checklist: IChecklist,
  task: ITask,
}>()

const checklist = ref(props.checklist)
const activeChecklistFormId = ref<string | null>(null)
provide('activeFormId', activeChecklistFormId)
const selectedItems = ref<number[]>([])

const setActiveForm = (id: string | null) => {
  activeChecklistFormId.value = id
}
provide('setActiveForm', setActiveForm)

const createChecklistItem = (data: any) => {
  api.post<ICreateChecklistItemResponse>(`v1/task-manager/tasks/${props.task.id}/checklists/${checklist.value.id}/items`, {
    title: data.value
  }).then(response => {
    handleApiSuccess(response)

    const responseData = response.data.data
    checklist.value.relationships.checklistItems.data.push({
      id: responseData.id,
      title: responseData.attributes.title,
      created_at: responseData.attributes.created_at,
      finished_at: responseData.attributes.finished_at
    })
  }).catch((error: AxiosError<{ message: string }>) => {
    handleApiError(error)
  })
}

const updateChecklistItem = () => {
  console.log(selectedItems.value)
}
</script>

<style scoped>

</style>
