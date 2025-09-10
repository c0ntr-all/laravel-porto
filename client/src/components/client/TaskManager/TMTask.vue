<template>
  <q-card class="task">
    <q-card-section class="task__header">
      <TMTaskTitle
        ref="taskTitleRef"
        v-model:title="task.title"
        @updated="handleUpdateTitle"
      />
      <q-btn @click="closeTask" class="task__close" icon="close" size="md" flat rounded dense/>
    </q-card-section>

    <q-separator/>

    <div class="row">
      <div class="col-9">
        <q-card-section class="task__body">
          <TMTaskContent
            ref="taskContentRef"
            v-model:content="task.content"
            @updated="handleUpdateContent"
          />
        </q-card-section>

        <q-card-section v-if="reminder">
          <TMReminder :reminder="reminder" />
        </q-card-section>

        <q-card-section v-if="checklists?.length">
          <TMChecklist
            v-for="checklist in checklists"
            :key="checklist.id"
            :checklist="checklist"
            :task="task"
          />
        </q-card-section>

        <q-card-section v-if="progress?.length">
          <TMTaskProgress
            :progress="progress"
            :task="task"
          />
        </q-card-section>

        <q-card-section>
          <TMTaskComments
            :comments-data="task?.relationships?.comments?.data"
            :task-id="task.id"
            @created="onCommentCreated"
          />
        </q-card-section>
      </div>
      <div class="col-3">
        <q-card-section class="column q-gutter-sm">
          <TMChecklistAddButton
            :task-id="task.id"
            @created="onChecklistCreated"
          />
          <TMProgressAddButton
            :task-id="task.id"
            :is-progress-available="isProgressAvailable"
            @created="onProgressCreated"
          />
          <TMReminderAddButton
            :task-id="task.id"
            :is-reminder-available="isReminderAvailable"
            @created="onReminderCreated"
          />
        </q-card-section>
      </div>
    </div>
  </q-card>
</template>

<script lang="ts" setup>
import { computed, provide, ref } from 'vue'
import TMChecklist from 'src/components/client/TaskManager/TMChecklist.vue'
import TMTaskProgress from 'src/components/client/TaskManager/TMTaskProgress.vue'
import TMReminder from 'src/components/client/TaskManager/TMReminder.vue'
import TMTaskComments from 'src/components/client/TaskManager/TMTaskComments.vue'
import TMChecklistAddButton from 'src/components/client/TaskManager/TMChecklistAddButton.vue'
import TMProgressAddButton from 'src/components/client/TaskManager/TMProgressAddButton.vue'
import TMReminderAddButton from 'src/components/client/TaskManager/TMReminderAddButton.vue'
import TMTaskContent from 'src/components/client/TaskManager/TMTaskContent.vue'
import TMTaskTitle from 'src/components/client/TaskManager/TMTaskTitle.vue'
import { useTaskStore } from 'src/stores/modules/TaskManager/taskStore'
import { IChecklist, IComment, IProgressItem, IReminderItem, ITask } from 'src/types/TaskManager/task'

const props = defineProps<{ task: ITask }>()
const emit = defineEmits<{
  (e: 'closed'): void
}>()
const task = ref<ITask>(props.task)
task.value.content = task.value.content || '' // null to string cast
const taskTitleRef = ref(null)
const taskContentRef = ref(null)
const isProgressAvailable = computed(() => {
  const progressData = task.value?.relationships?.progress?.data
  return !progressData || progressData.filter(item => item.is_final).length === 0
})
const isReminderAvailable = computed(() => {
  return !task.value?.relationships?.reminder?.data
})
const checklists = ref(task.value.relationships?.checklists?.data || [])
const progress = ref(task.value.relationships?.progress?.data || [])
const reminder = ref(task.value.relationships?.reminder?.data || null)
const activeChecklistFormId = ref<string | null>(null)

provide('activeFormId', activeChecklistFormId)

const taskStore = useTaskStore()

async function handleUpdateTitle(newTitle: string) {
  await taskStore.updateTaskTitle(task.value.id, newTitle).then(() => {
    taskTitleRef.value?.onUpdateSuccess()
  }).catch(() => {
    taskTitleRef.value?.onUpdateError()
  })
}

async function handleUpdateContent(newContent: string) {
  await taskStore.updateTaskContent(task.value.id, newContent).then(() => {
    taskContentRef.value?.onUpdateSuccess()
  }).catch(() => {
    taskContentRef.value?.onUpdateError()
  })
}

const onCommentCreated = (newComment: IComment) => {
  task.value.relationships.comments.data.push(newComment)
}

const onChecklistCreated = (newChecklist: IChecklist) => {
  task.value.relationships.checklists = {
    data: task.value?.relationships?.checklists?.data || [],
    meta: {
      count: 1
    }
  }
  checklists.value.push(newChecklist)
}

const onProgressCreated = (newProgressItem: IProgressItem) => {
  task.value.relationships.progress = {
    data: task.value?.relationships?.progress?.data || [],
    meta: {
      count: 1
    }
  }
  progress.value.push(newProgressItem)
}

// TODO: Возможно, нужно убрать приставку Item
const onReminderCreated = (newReminderItem: IReminderItem) => {
  reminder.value = newReminderItem
}

// Only one form should be opened
const setActiveChecklistForm = (id: string | null) => {
  activeChecklistFormId.value = id
}
provide('setActiveChecklistForm', setActiveChecklistForm)

const closeTask = () => {
  emit('closed')
}
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
