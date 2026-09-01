<template>
  <q-card class="list bg-grey-4">
    <q-card-section class="list__header">
      <q-input
        v-if="isEditingTitle"
        v-model="titleDraft"
        class="list__header-input"
        maxlength="30"
        dense
        borderless
        autofocus
        @keyup.enter.prevent="saveTitle"
        @keyup.esc="cancelEditTitle"
        @blur="saveTitle"
      />
      <p
        v-else
        class="list__title"
        @click="startEditTitle"
      >
        {{ list.title }}
      </p>
      <q-btn
        class="list__delete"
        icon="delete_outline"
        size="sm"
        flat
        round
        dense
        @click="confirmDelete"
      >
        <q-tooltip>Удалить список</q-tooltip>
      </q-btn>
    </q-card-section>
    <q-separator dark/>
    <q-card-section class="list__body">
      <template v-if="tasks.length">
        <TMTaskListItem
          v-for="task in tasks"
          :key="task.id"
          :task="task"
          @opened="openTask"
        />
      </template>
    </q-card-section>
    <q-card-section class="list__footer">
      <div v-if="!showAddForm" @click="openAddForm" class="list__add-button">
        <q-icon name="add" size="sm"/>
        <span>Create a card</span>
      </div>
      <div v-if="showAddForm" class="list__add-form">
        <q-input
          @keyup.enter="createTask"
          v-model="model.taskTitle"
          ref="taskAddTextarea"
          type="textarea"
          input-style="height: 60px; resize: none"
          class="list__add-textarea q-mb-sm"
          dense
          outlined
        />
        <q-btn
          @click="createTask"
          label="Create a card"
          color="secondary"
          class="q-mr-sm"
          no-caps
          dense
        />
        <q-btn
          @click="closeAddForm"
          icon="close"
          color="danger"
          size="md"
          flat
          round
          dense
        />
      </div>
    </q-card-section>
  </q-card>
</template>

<script lang="ts" setup>
import { ref, computed, nextTick } from 'vue'
import { Dialog } from 'quasar'
import { useTaskStore } from 'src/stores/modules/taskStore'
import { handleApiError } from 'src/utils/jsonapi'
import { ITaskList } from 'src/types/TaskManager/task'
import TMTaskListItem from 'src/components/client/TaskManager/TMTaskListItem.vue'
import { useTaskDialogRoute } from 'src/composables/client/TaskManager/useTaskDialogRoute'

const taskStore = useTaskStore()
const { openTask } = useTaskDialogRoute()

const props = defineProps<{
  list: ITaskList
}>()

const showAddForm = ref<boolean>(false)
const isEditingTitle = ref(false)
const titleDraft = ref(props.list.title)
const taskAddTextarea = ref<HTMLElement | null>(null)
const model = ref<{ taskTitle: string, taskContent: string }>({
  taskTitle: '',
  taskContent: ''
})

const tasks = computed(() => {
  return props.list.tasksIds?.map(id => taskStore.tasks.byId[id])
})

const openAddForm = () => {
  showAddForm.value = true
  nextTick(() => {
    taskAddTextarea.value?.focus()
  })
}

const closeAddForm = () => {
  showAddForm.value = false
}

const startEditTitle = () => {
  titleDraft.value = props.list.title
  isEditingTitle.value = true
}

const cancelEditTitle = () => {
  titleDraft.value = props.list.title
  isEditingTitle.value = false
}

const saveTitle = async () => {
  if (!isEditingTitle.value) return

  const nextTitle = titleDraft.value.trim()
  isEditingTitle.value = false

  if (!nextTitle || nextTitle === props.list.title) {
    titleDraft.value = props.list.title
    return
  }

  await taskStore.updateTaskList(props.list.id, { title: nextTitle })
}

const confirmDelete = () => {
  Dialog.create({
    title: 'Удалить список?',
    message: `Список «${props.list.title}» и все задачи в нём будут удалены.`,
    cancel: { label: 'Отмена', flat: true },
    ok: { label: 'Удалить', color: 'negative' },
    persistent: true
  }).onOk(() => {
    taskStore.deleteTaskList(props.list.id)
  })
}

const clearModel = () => {
  model.value.taskTitle = ''
}

const createTask = async (): Promise<void> => {
  await taskStore.createTask({
    title: model.value.taskTitle,
    task_list_id: props.list.id
  }).catch(error => {
    handleApiError(error)
  }).finally(() => {
    clearModel()
  })
}
</script>

<style lang="scss" scoped>
.list {
  position: relative;
  display: flex;
  flex-direction: column;
  width: 272px;
  max-height: 100%;
  border-radius: 3px;
  white-space: normal;
  box-sizing: border-box;

  &__header {
    display: flex;
    align-items: flex-start;
    gap: 4px;
    padding: 8px;
  }

  &__title {
    flex: 1;
    min-width: 0;
    margin: 0;
    padding: 4px 6px;
    border-radius: 3px;
    font-weight: 600;
    line-height: 1.35;
    overflow-wrap: anywhere;
    cursor: pointer;

    &:hover {
      background-color: #091e4214;
    }
  }

  &__header-input {
    flex: 1;
    min-width: 0;

    :deep(.q-field__control) {
      padding: 0 6px;
      background: #fff;
      border-radius: 3px;
      box-shadow: inset 0 0 0 2px #0079bf;
    }
  }

  &__delete {
    flex-shrink: 0;
    margin-top: 1px;
  }

  &__body {
    flex: 1 1 auto;
    min-height: 0;
    overflow-x: hidden;
    overflow-y: auto;
    padding: 8px;
  }

  &__footer {
    padding: 10px 8px;
  }

  &__add-button {
    display: flex;
    align-items: center;
    border-radius: 3px;
    padding: 5px 0;

    &:hover {
      cursor: pointer;
      background-color: #091e4214;
    }
  }
}
</style>
