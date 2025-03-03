<template>
  <q-card-section class="checklist">
    <div class="text-h6 q-mb-sm">{{ checklist.title }}</div>
    <div class="checklist-progress">
      <q-badge color="white" text-color="accent" :label="progressLabel" />
      <q-linear-progress
        size="15px"
        :value="progress"
        color="accent"
        animation-speed="500"
      />
    </div>
    <template v-if="checklist.relationships.checklistItems.data.length">
      <q-list class="q-mb-md" bordered separator>
        <TMChecklistItem
          v-for="checklistItem in checklist?.relationships?.checklistItems?.data"
          :key="checklistItem.id"
          :item="checklistItem"
          v-model="selectedItems"
          dense
        />
      </q-list>
    </template>
    <TMChecklistItemAddButton
      @processed="createChecklistItem"
      :checklist-id="checklist.id"
    />
  </q-card-section>
</template>

<script lang="ts" setup>
import { computed, provide, ref } from 'vue'
import { api } from 'src/boot/axios'
import { handleApiError, handleApiSuccess } from 'src/utils/jsonapi'
import { AxiosError } from 'axios'
import TMChecklistItemAddButton from 'src/components/client/TaskManager/TMChecklistItemAddButton.vue'
import { IChecklist, IChecklistItem, ITask } from 'src/components/client/TaskManager/types'
import TMChecklistItem from 'src/components/client/TaskManager/TMChecklistItem.vue'

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

interface IUpdateChecklistItemResponse {
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
const checklistItems = ref(checklist.value.relationships.checklistItems.data)
const selectedItems = ref(
  checklist.value.relationships.checklistItems.data
  .filter(item => item.finished_at !== null)
  .map(item => item.id)
)
const progress = computed(() => {
  if (checklistItems.value.length === 0) return 0
  return Number((selectedItems.value.length / checklistItems.value.length).toFixed(2))
})

const progressLabel = computed(() => (progress.value * 100) + '%')

const createChecklistItem = async (data: any) => {
  await api.post<ICreateChecklistItemResponse>(`v1/task-manager/tasks/${props.task.id}/checklists/${checklist.value.id}/items`, {
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

const updateChecklistItem = async (checklistItem: IChecklistItem, data: {title?: string, is_finished?: boolean}) => {
  return await api.patch<IUpdateChecklistItemResponse>(
    `v1/task-manager/tasks/${props.task.id}/checklists/${checklist.value.id}/items/${checklistItem.id}`,
    {
    ...data
  }).then(response => {
    handleApiSuccess(response)

    const responseData = response.data.data
    const checklistItemForChange = checklist.value.relationships.checklistItems.data.find(item => item.id === checklistItem.id)

    if (checklistItemForChange) {
      checklistItemForChange.title = responseData.attributes.title
    } else {
      throw new Error('Can\'t find needed task item')
    }
  }).catch((error: AxiosError<{ message: string }>) => {
    handleApiError(error)
  })
}
provide('updateChecklistItem', updateChecklistItem)
</script>

<style scoped>
.checklist-progress {
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 1rem;
}
</style>
