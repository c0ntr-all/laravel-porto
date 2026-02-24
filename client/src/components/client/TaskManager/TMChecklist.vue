<template>
  <div class="checklist">
    <div class="text-h6 q-mb-sm">
      {{ checklist.title }}
      <q-popup-edit
        ref="checklistTitlePopupRef"
        v-model="checklistTitle"
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
    <template v-if="checklistItems.length">
      <q-list class="q-mb-md" bordered separator>
        <TMChecklistItem
          v-for="item in checklistItems"
          :key="item.id"
          :item="item"
          :task-id="task.id"
          :checklist-id="checklist.id"
          v-model="selectedItems"
          @decline="onDeclineItem"
          dense
        />
      </q-list>
    </template>
    <TMChecklistItemAddButton
      ref="checklistItemAddRef"
      :checklist-id="checklist.id"
    />
  </div>
  <TMChecklistItemDeclineDialog
    v-model="isOpenDeclineItemDialog"
    @declined="processDeclineItem"
  />
</template>

<script lang="ts" setup>
import { computed, ref, provide } from 'vue'
import { useTaskStore } from 'src/stores/modules/taskStore'
import TMChecklistItemAddButton from 'src/components/client/TaskManager/TMChecklistItemAddButton.vue'
import { IChecklist, IChecklistItem, ITask } from 'src/types/TaskManager/task'
import { createChecklistItemKey, updateChecklistItemKey, deleteChecklistItemKey } from 'src/symbols/task-manager.keys'
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

// --- Computed ---
const checklistItems = computed(() =>
  (props.checklist.checklistItemsIds || []).map(id => taskStore.checklistItems.byId[id])
)
const progress = computed(() => {
  if (checklistItems.value.length === 0) {
    return 0
  }

  return Number((selectedItems.value.length / checklistItems.value.length).toFixed(2))
})
const progressLabel = computed(() => (progress.value * 100) + '%')

// --- State ---
const checklistTitle = ref<string>(props.checklist.title)
const isOpenDeclineItemDialog = ref<boolean>(false)
const itemForDecline = ref<IChecklistItem | null>(null) // Элемент чеклиста, который пользователь решил отклонить.
const selectedItems = computed({
  get() {
    return checklistItems.value
      .filter(item => item.finished_at !== null)
      .map(item => item.id)
  },

  set(newIds: string[]) {
    // синхронизируем стор
    checklistItems.value.forEach(item => {
      const shouldBeFinished = newIds.includes(item.id)

      if (shouldBeFinished && !item.finished_at) {
        taskStore.finishChecklistItem(props.task.id, props.checklist.id, item.id)
      }

      if (!shouldBeFinished && item.finished_at) {
        taskStore.unfinishChecklistItem(props.task.id, props.checklist.id, item.id)
      }
    })
  }
})

// --- Refs ---
const checklistTitlePopupRef = ref<InstanceType<typeof import('quasar').QPopupEdit> | null>(null)
const checklistItemAddRef = ref<IChecklistItemAddRef | null>(null)

// --- Methods ---
const closeChecklistPopup = () => {
  if (checklistTitlePopupRef?.value) {
    checklistTitlePopupRef?.value.cancel()
  }
}
const createChecklistItem = async (title: string) => {
  await taskStore.createChecklistItem(props.task.id, props.checklist.id, { title })
}

const updateChecklistTitle = async (title: string) => {
  if (title !== props.checklist.title) {
    await taskStore.updateChecklist(props.task.id, props.checklist.id, { title })
      .then(() => {
        closeChecklistPopup()
      })
  }

  closeChecklistPopup()
}

// Открывает модальное окно для причины отклонения и назначает элемент для отклонения
const onDeclineItem = (checklistItem: IChecklistItem) => {
  itemForDecline.value = checklistItem
  isOpenDeclineItemDialog.value = true
}
const processDeclineItem = async (declineReason: string) => {
  if (itemForDecline.value) {
    await taskStore.updateChecklistItem(props.task.id, props.checklist.id, itemForDecline.value.id, {
      is_declined: true,
      decline_reason: declineReason,
      is_finished: false
    })
  }
}

const updateChecklistItem = async (itemId: string, patch: Partial<IChecklistItem>) => {
  await taskStore.updateChecklistItem(props.task.id, props.checklist.id, itemId, patch)
}

const deleteChecklistItem = async (itemId: string) => {
  await taskStore.deleteChecklistItem(props.task.id, props.checklist.id, itemId)
}

provide(createChecklistItemKey, createChecklistItem)
provide(updateChecklistItemKey, updateChecklistItem)
provide(deleteChecklistItemKey, deleteChecklistItem)
</script>

<style scoped>
.checklist-progress {
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 1rem;
}
</style>
