<template>
  <q-card class="list bg-grey-4">
    <q-card-section class="list__header">
      <p>{{ list.attributes.title }}</p>
    </q-card-section>
    <q-separator dark/>
    <q-card-section class="list__body">
      <template v-if="tasks.length">
        <TMTask
          v-for="task in tasks"
          :key="task.id"
          :task="task"
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
          v-model="model.newCardName"
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
import { ref, nextTick } from 'vue'
import { AxiosError } from 'axios'
import { api } from 'src/boot/axios'
import TMTask from 'src/components/client/TaskManager/TMTask.vue'
import { handleApiError, handleApiSuccess } from 'src/utils/jsonapi'
import { IComment, ITask, ITaskList } from 'src/components/client/TaskManager/types'

interface CreateTaskResponse {
  data: {
    type: string
    id: string
    attributes: {
      title: string
      completed: boolean
      content?: string
      created_at?: string
      relationships: {
        comments: {
          data: IComment[]
        }
      }
    }
  },
  meta: {
    message?: string
  }
}

const props = defineProps<{
  list: ITaskList
}>()
const showAddForm = ref<boolean>(false)
const taskAddTextarea = ref<HTMLElement | null>(null)
const tasks = ref<ITask[]>(props.list.relationships.tasks?.data || [])
const model = ref<{ newCardName: string }>({
  newCardName: ''
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

const createTask = async (): Promise<void> => {
  const cardName = model.value.newCardName
  model.value.newCardName = ''

  await api.post<CreateTaskResponse>('v1/task-manager/tasks', {
    title: cardName,
    task_list_id: props.list.id
  }).then(response => {
    handleApiSuccess(response)

    const newTask: ITask = {
      id: response.data.data.id,
      title: response.data.data.attributes.title,
      content: response.data.data.attributes.content,
      finished_at: null,
      is_declined: false,
      relationships: {
        comments: {
          data: [],
          meta: {
            count: 0
          }
        }
      }
    }

    tasks.value.push(newTask)
  }).catch((error: AxiosError<{ message: string }>) => {
    handleApiError(error)
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
    padding: 8px;

    p {
      margin: 0;
    }

    &-name {
      background: #0000;
      border-radius: 3px;
      box-shadow: none;
      font-weight: 600;
      height: 28px;
      margin: -4px 0;
      max-height: 256px;
      min-height: 20px;
      padding: 4px 8px;
      border: none;
      resize: none;
      overflow: hidden;
      overflow-wrap: break-word;

      &.is-active {
        background-color: #fff;
        box-shadow: inset 0 0 0 2px #0079bf;
      }
    }

    &--cover {
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      top: 0;
      cursor: pointer;
    }
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
