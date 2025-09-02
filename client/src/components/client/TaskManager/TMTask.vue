<template>
  <q-card class="task">
    <q-card-section class="task__header">
      <div class="task__title text-h6">
        {{ task.title }}
        <q-popup-edit
          ref="titlePopup"
          v-model="task.title"
          auto-save
          v-slot="scope"
        >
          <q-input
            v-model="scope.value"
            @keyup.enter="updateTitle(scope.value)"
            dense
            autofocus
            counter
          />
          <div class="q-pt-sm">
            <q-btn @click="updateTitle(scope.value)" label="Save" color="primary" flat/>
            <q-btn @click="titlePopup?.cancel()" label="Cancel" color="primary" flat/>
          </div>
        </q-popup-edit>
      </div>
      <q-btn @click="closeTask" class="task__close" icon="close" size="md" flat rounded dense/>
    </q-card-section>

    <q-separator/>

    <div class="row">
      <div class="col-9">
        <q-card-section class="task__body">
          <div v-if="task.content" v-html="task.content" class="task__content"></div>
          <div v-else class="task__content text-grey-5">There is no description!</div>
          <q-popup-edit
            ref="contentPopup"
            v-model="task.content"
            v-slot="scope"
          >
            <q-editor
              v-model="scope.value"
              min-height="5rem"
              autofocus
              @keyup.enter.stop
            />
            <div class="q-pt-sm">
              <q-btn @click="updateContent(scope.value)" label="Save" color="primary" flat/>
              <q-btn @click="contentPopup?.cancel()" label="Cancel" color="primary" flat/>
            </div>
          </q-popup-edit>
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
import { AxiosError } from 'axios'
import { api } from 'src/boot/axios'
import { handleApiError, handleApiSuccess } from 'src/utils/jsonapi'
import {
  IChecklist, IComment, IProgressItem, IReminderItem, ITask, IUpdateTaskResponse
} from 'src/components/client/TaskManager/types'
import TMChecklist from 'src/components/client/TaskManager/TMChecklist.vue'
import TMTaskProgress from 'src/components/client/TaskManager/TMTaskProgress.vue'
import TMReminder from 'src/components/client/TaskManager/TMReminder.vue'
import TMTaskComments from 'src/components/client/TaskManager/TMTaskComments.vue'
import TMChecklistAddButton from 'src/components/client/TaskManager/TMChecklistAddButton.vue'
import TMProgressAddButton from 'src/components/client/TaskManager/TMProgressAddButton.vue'
import TMReminderAddButton from 'src/components/client/TaskManager/TMReminderAddButton.vue'

const props = defineProps<{ task: ITask }>()
const emit = defineEmits<{
  (e: 'closed'): void
}>()
const titlePopup = ref<InstanceType<typeof import('quasar').QPopupEdit> | null>(null)
const contentPopup = ref<InstanceType<typeof import('quasar').QPopupEdit> | null>(null)
const task = ref<ITask>(props.task)
const isProgressAvailable = computed(() => {
  const progressData = task.value?.relationships?.progress?.data
  return !progressData || progressData.filter(item => item.is_final === true).length === 0
})
const isReminderAvailable = computed(() => {
  return !task.value?.relationships?.reminder?.data
})
const checklists = ref(task.value.relationships?.checklists?.data || [])
const progress = ref(task.value.relationships?.progress?.data || [])
const reminder = ref(task.value.relationships?.reminder?.data || null)
const activeChecklistFormId = ref<string | null>(null)

provide('activeFormId', activeChecklistFormId)

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
  task.value.relationships.reminder.data.push(newReminderItem)
}

// Only one form should be opened
const setActiveChecklistForm = (id: string | null) => {
  activeChecklistFormId.value = id
}
provide('setActiveChecklistForm', setActiveChecklistForm)

const closeTask = () => {
  emit('closed')
}

const updateTitle = (value: string) => {
  api.patch<IUpdateTaskResponse>(`v1/task-manager/tasks/${task.value.id}`, {
    title: value
  }).then(response => {
    handleApiSuccess(response)
    titlePopup.value?.set()
  }).catch((error: AxiosError<{ message: string }>) => {
    handleApiError(error)
    titlePopup.value?.cancel()
  })
}

const updateContent = (value: string) => {
  api.patch<IUpdateTaskResponse>(`v1/task-manager/tasks/${task.value.id}`, {
    content: value
  }).then(response => {
    handleApiSuccess(response)
    contentPopup.value?.set()
  }).catch((error: AxiosError<{ message: string }>) => {
    handleApiError(error)
    contentPopup.value?.cancel()
  })
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

  &__title {
    width: 100%;

    &:hover {
      cursor: pointer;
      background: #f4f5f7;
    }
  }

  &__content {
    &:hover {
      cursor: pointer;
      background: #f4f5f7;
    }
  }
}
</style>
