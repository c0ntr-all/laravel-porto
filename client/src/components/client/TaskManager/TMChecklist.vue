<template>
  <div class="checklist">
    <div class="text-h6 q-mb-sm">
      {{ checklist.title }}
      <q-popup-edit
        ref="checklistTitlePopupRef"
        v-model="checklist.title"
        v-slot="scope"
        auto-save
      >
        <q-input
          v-model="scope.value"
          @keyup.enter="updateChecklistTitle(scope.value)"
          dense
          autofocus
          counter
        />
        <div class="q-pt-sm">
          <q-btn @click="updateChecklistTitle(scope.value)" label="Save" color="primary" flat/>
          <q-btn @click="checklistTitlePopupRef?.cancel()" label="Cancel" color="primary" flat/>
        </div>
      </q-popup-edit>
    </div>
    <div class="checklist-progress">
      <q-badge color="white" text-color="accent" :label="progressLabel" />
      <q-linear-progress
        size="15px"
        :value="progress"
        color="accent"
        animation-speed="500"
      />
    </div>
    <template v-if="checklist.checklistItems.data.length">
      <q-list class="q-mb-md" bordered separator>
        <TMChecklistItem
          v-for="item in checklist?.checklistItems?.data"
          :key="item.id"
          :item="item"
          v-model="selectedItems"
          @decline="initDeclineItem"
          @activate="onActivateItem"
          @update="patch => onUpdateItem(item, patch)"
          dense
        />
      </q-list>
    </template>
    <TMChecklistItemAddButton
      ref="checklistItemAddRef"
      @processed="createChecklistItem"
      :checklist-id="checklist.id"
    />
  </div>
  <TMChecklistItemDeclineDialog
    v-model="isOpenDeclineItemDialog"
    @declined="processDeclineItem"
  />
</template>

<script lang="ts" setup>
import { computed, ref } from 'vue'
import { AxiosError } from 'axios'
import { api } from 'src/boot/axios'
import { handleApiError, handleApiSuccess } from 'src/utils/jsonapi'
import { useTaskStore } from 'src/stores/modules/taskStore'
import TMChecklistItemAddButton from 'src/components/client/TaskManager/TMChecklistItemAddButton.vue'
import { IChecklist, ITask, IChecklistItemResponse } from 'src/types/TaskManager/task'
import TMChecklistItem from 'src/components/client/TaskManager/TMChecklistItem.vue'
import TMChecklistItemDeclineDialog from 'src/components/client/TaskManager/TMChecklistItemDeclineDialog.vue'

interface IChecklistItemAddRef {
  clearModel: () => void
}

// --- Store ---
const taskStore = useTaskStore()

// --- Props ---
const props = defineProps<{
  checklist: IChecklist,
  task: ITask,
}>()

// --- State ---
const checklist = ref<IChecklist>(props.checklist)
const checklistItems = ref(checklist.value.checklistItems.data)
const selectedItems = ref(
  checklist.value.checklistItems.data
    .filter(item => item.finished_at !== null)
    .map(item => item.id)
)
const isOpenDeclineItemDialog = ref(false)
// Элемент чеклиста, который пользователь решил отклонить.
const itemForDecline = ref(null)

// --- Computed ---
const progress = computed(() => {
  if (checklistItems.value.length === 0) return 0
  return Number((selectedItems.value.length / checklistItems.value.length).toFixed(2))
})
const progressLabel = computed(() => (progress.value * 100) + '%')

// --- Refs ---
const checklistTitlePopupRef = ref<InstanceType<typeof import('quasar').QPopupEdit> | null>(null)
const checklistItemAddRef = ref<IChecklistItemAddRef | null>(null)

// --- Methods ---
const createChecklistItem = async (data: any) => {
  await api.post<IChecklistItemResponse>(`v1/task-manager/tasks/${props.task.id}/checklists/${checklist.value.id}/items`, {
    title: data.value
  }).then(response => {
    handleApiSuccess(response.data)

    const responseData = response.data.data
    checklist.value.checklistItems.data.push({
      id: responseData.id,
      title: responseData.attributes.title,
      created_at: responseData.attributes.created_at,
      finished_at: responseData.attributes.finished_at
    })
    if (checklistItemAddRef.value) {
      checklistItemAddRef.value.clearModel()
    }
  }).catch((error: AxiosError<{ message: string }>) => {
    handleApiError(error)
  })
}

const updateChecklistTitle = async (newTitle: string) => {
  return await api.patch<IChecklistItemResponse>(
    `v1/task-manager/tasks/${props.task.id}/checklists/${checklist.value.id}`,
    { title: newTitle })
    .then(response => {
      handleApiSuccess(response.data)

      const responseData = response.data.data
      checklist.value.title = responseData.attributes.title
      checklist.value.updated_at = responseData.attributes.updated_at

      if (checklistTitlePopupRef?.value) {
        checklistTitlePopupRef?.value.cancel()
      }
    })
    .catch((error: AxiosError<{ message: string }>) => {
      handleApiError(error)

      return Promise.reject(error)
    })
}

// Открывает модальное окно для причины отклонения и назначает элемент для отклонения
const initDeclineItem = checklistItem => {
  itemForDecline.value = checklistItem
  isOpenDeclineItemDialog.value = true
}
const onActivateItem = async checklistItem => {
  try {
    const responseData = await taskStore.updateChecklistItem(props.task.id, checklist.value.id, checklistItem.id, {
      is_declined: false,
      decline_reason: ''
    })

    const object = {
      id: responseData.data.id,
      type: responseData.data.type,
      ...responseData.data.attributes
    }

    checklistItems.value.map(item => {
      if (item.id === object.id) {
        item.is_declined = responseData.data.attributes.is_declined
        item.decline_reason = responseData.data.attributes.decline_reason
      }

      return item
    })
  } catch (error) {
    const id = itemForDecline.value.id
    const idx = checklistItems.value.filter(item => item === id).indexOf(id)
    if (idx !== -1) {
      checklistItems.value.splice(idx, 1)
    }
  }
}

const processDeclineItem = async declineReason => {
  try {
    const id = itemForDecline.value.id
    const responseData = await taskStore.updateChecklistItem(props.task.id, checklist.value.id, id, {
      is_declined: true,
      decline_reason: declineReason,
      is_finished: false
    })

    checklistItems.value.map(item => {
      if (item.id === id) {
        item.is_declined = responseData.data.attributes.is_declined
        item.decline_reason = responseData.data.attributes.decline_reason
        item.finished_at = responseData.data.attributes.finished_at
      }

      return item
    })

    const idx = selectedItems.value.indexOf(id)
    if (idx !== -1) {
      selectedItems.value.splice(idx, 1)
    }
  } catch (error) {
    const id = itemForDecline.value.id
    const idx = checklistItems.value.filter(item => item === id).indexOf(id)
    if (idx !== -1) {
      checklistItems.value.splice(idx, 1)
    }
  }
}

const onUpdateItem = async (item, patch) => {
  const responseData = await taskStore.updateChecklistItem(props.task.id, checklist.value.id, item.id, patch)

  const object = {
    id: responseData.data.id,
    type: responseData.data.type,
    ...responseData.data.attributes
  }

  checklistItems.value.map(item => {
    if (item.id === object.id) {
      item.is_declined = responseData.data.attributes.is_declined
      item.decline_reason = responseData.data.attributes.decline_reason
    }

    return item
  })
}
</script>

<style scoped>
.checklist-progress {
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 1rem;
}
</style>
