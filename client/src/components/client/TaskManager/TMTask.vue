<template>
  <TMTaskSkeleton v-if="!task.isHydrated" />
  <q-card v-else class="task">
    <q-card-section class="task__header">
      <TMTaskTitle
        ref="taskTitleRef"
        v-model:title="title"
        @saved="handleUpdateTitle"
      />
      <q-btn @click="closeTask" class="task__close" icon="close" size="md" flat rounded dense/>
    </q-card-section>

    <q-separator/>

    <div class="row">
      <div class="col-9">
        <q-card-section class="task__body">
          <TMTaskContent
            ref="taskContentRef"
            v-model:content="content"
            @updated="handleUpdateContent"
          />
        </q-card-section>

        <q-card-section v-if="reminder">
          <TMReminder :reminder="reminder" />
        </q-card-section>

        <q-card-section v-if="checklists.length">
          <TMChecklist
            v-for="checklist in checklists"
            :key="checklist.id"
            :checklist="checklist"
            :task="task"
          />
        </q-card-section>

        <q-card-section v-if="progresses?.length">
          <TMTaskProgress
            :progress="progresses"
            :task="task"
          />
        </q-card-section>

        <q-card-section>
          <TMTaskComments
            :comments="comments"
            :task-id="task.id"
          />
        </q-card-section>
      </div>
      <div class="col-3">
        <q-card-section class="column q-gutter-sm">
          <TMChecklistAddButton
            :task-id="task.id"
          />
          <TMProgressAddButton
            :task-id="task.id"
            :is-progress-available="isProgressAvailable"
          />
          <TMReminderAddButton
            :task-id="task.id"
            :is-reminder-available="!!reminder"
          />
        </q-card-section>
      </div>
    </div>
  </q-card>
</template>

<script lang="ts" setup>
import { computed, provide, ref, onMounted } from 'vue'
import { useTaskStore } from 'src/stores/modules/taskStore'
import TMChecklist from 'src/components/client/TaskManager/TMChecklist.vue'
import TMTaskProgress from 'src/components/client/TaskManager/TMTaskProgress.vue'
import TMReminder from 'src/components/client/TaskManager/TMReminder.vue'
import TMTaskComments from 'src/components/client/TaskManager/TMTaskComments.vue'
import TMChecklistAddButton from 'src/components/client/TaskManager/TMChecklistAddButton.vue'
import TMProgressAddButton from 'src/components/client/TaskManager/TMProgressAddButton.vue'
import TMReminderAddButton from 'src/components/client/TaskManager/TMReminderAddButton.vue'
import TMTaskContent from 'src/components/client/TaskManager/TMTaskContent.vue'
import TMTaskTitle from 'src/components/client/TaskManager/TMTaskTitle.vue'
import TMTaskSkeleton from 'src/components/client/TaskManager/TMTaskSkeleton.vue'
import { IChecklist, IProgress, IReminderItem, ITask } from 'src/types/TaskManager/task'
import { IComment } from 'src/types'

interface ITaskPartsRef {
  onSaveSuccess: () => void
  onSaveError: () => void
}

const taskStore = useTaskStore()

const props = defineProps<{ taskId: string }>()
const emit = defineEmits<{
  (e: 'closed'): void
}>()

const task = computed<ITask>(() => taskStore.tasks.byId[props.taskId])

const content = ref<string>(task.value.content || '') // null to string cast
const title = ref<string>(task.value.title || '')

const checklists = computed<IChecklist[]>(() =>
  task.value.checklistsIds!.map(id => taskStore.checklists?.byId[id])
)
const progresses = computed<IProgress[] | undefined>(() =>
  task.value.progressIds?.map(id => taskStore.progress.byId[id])
)
const comments = computed<IComment[]>(() =>
  task.value.commentsIds?.map(id => taskStore.comments.byId[id])
)
const reminder = computed<IReminderItem | null>(() => {
  const reminder = task.value.reminderIds?.map(id => taskStore.reminder.byId[id])
  if (reminder) {
    return reminder[0]
  }

  return null
})
const isProgressAvailable = computed(() => progresses?.value?.filter(item => item.is_final).length === 0)

const taskTitleRef = ref<ITaskPartsRef | null>(null)
const taskContentRef = ref<ITaskPartsRef | null>(null)
const activeChecklistFormId = ref<string | null>(null)

provide('activeFormId', activeChecklistFormId)

async function loadTask() {
  await taskStore.getTask(props.taskId)
}

async function handleUpdateTitle(newTitle: string) {
  await taskStore.updateTask(task.value.id, {
    title: newTitle
  }).then(() => {
    taskTitleRef.value?.onSaveSuccess()
  }).catch(() => {
    taskTitleRef.value?.onSaveError()
  })
}

async function handleUpdateContent(newContent: string) {
  await taskStore.updateTask(task.value.id, {
    content: newContent
  }).then(() => {
    taskContentRef.value?.onSaveSuccess()
  }).catch(() => {
    taskContentRef.value?.onSaveError()
  })
}

// Only one form should be opened
const setActiveChecklistForm = (id: string | null) => {
  activeChecklistFormId.value = id
}
provide('setActiveChecklistForm', setActiveChecklistForm)

const closeTask = () => {
  emit('closed')
}

onMounted(() => {
  loadTask()
})
</script>

<style lang="scss" scoped>
.task {
  width: 768px;
  max-width: 80vw;

  &__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
}
</style>
