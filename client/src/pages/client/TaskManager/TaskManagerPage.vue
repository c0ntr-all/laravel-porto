<template>
  <TasksPageSkeleton v-if="loading"/>
  <template v-else>
    <q-btn
      v-if="!showAddForm"
      @click="openAddForm"
      color="primary"
      icon="add"
      label="Добавить список"
      class="q-mb-lg"
      no-caps
    />
    <p>Всего списков: {{ listsCount }}</p>
    <div v-if="showAddForm" class="list__add-form q-mb-lg">
      <q-input
        @keyup.enter="addNewList"
        v-model="model.newListName"
        ref="listAddTextarea"
        class="list__add-input q-mb-sm"
        dense
        outlined
      />
      <q-btn @click="addNewList" label="Добавить список" color="primary" class="q-mr-sm" no-caps/>
      <q-btn @click="closeAddForm" icon="close" color="danger" size="md" flat round dense/>
    </div>
    <div class="task-lists row items-start q-gutter-md q-mb-lg">
      <AppTaskList
        v-for="list in taskLists"
        :list="list"
        :key="list.id"
        :ref="'list-ref-' + list.id"
      />
    </div>
  </template>
</template>

<script lang="ts" setup>
import { ref, onMounted, nextTick } from 'vue'
import { useQuasar } from 'quasar'
import { AxiosError } from 'axios'
import { api } from 'src/boot/axios'

import TasksPageSkeleton from 'src/pages/client/TaskManager/TaskManagerPageSkeleton.vue'
import AppTaskList from 'src/components/client/TaskManager/TaskList/AppTaskList.vue'

interface Task {
  id: string
  title: string
  completed: boolean
}

interface TaskList {
  id: string
  title: string
  tasks?: Task[]
}

interface ApiResponse {
  data: TaskList[]
  meta: {
    task_lists_count: number
    message?: string
  }
}

const $q = useQuasar()

const showAddForm = ref<boolean>(false)
const taskLists = ref<TaskList[]>([])
const listsCount = ref<number>(0)
const loading = ref<boolean>(true)
const listAddTextarea = ref<HTMLElement | null>(null)
const model = ref<{ newListName: string }>({
  newListName: ''
})

const openAddForm = () => {
  showAddForm.value = true
  nextTick(() => {
    listAddTextarea.value?.focus()
  })
}

const closeAddForm = () => {
  showAddForm.value = false
}

const addNewList = async () => {
  const listName = model.value.newListName
  model.value.newListName = ''

  await api.post<ApiResponse>('v1/task-manager/list/store', {
    title: listName
  }).then(response => {
    $q.notify({
      type: 'positive',
      message: response.data.meta.message || 'Произошла ошибка'
    })
    taskLists.value.push(response.data.data[0])
  }).catch(error => {
    const axiosError = error as AxiosError<{ message: string }>
    $q.notify({
      type: 'negative',
      message: axiosError.response?.data.message || 'Произошла ошибка'
    })
  })
}

const getTaskLists = async () => {
  loading.value = true
  api.get<ApiResponse>('v1/task-manager/task-lists')
    .then(response => {
      taskLists.value = response.data.data
      listsCount.value = response.data.meta.task_lists_count
    })
    .catch(error => {
      $q.notify({
        type: 'negative',
        message: (error as AxiosError).message
      })
    })
    .finally(() => {
      loading.value = false
    })
}

onMounted(() => {
  getTaskLists()
})
</script>

<style lang="scss" scoped>
.list {
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

  &__add-input {
    max-width: 300px;
  }
}
</style>
