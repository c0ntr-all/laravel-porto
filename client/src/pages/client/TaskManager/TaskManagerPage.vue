<template>
  <TaskManagerPageSkeleton v-if="loading" />
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
        @keyup.enter="createList"
        v-model="model.newListName"
        ref="listAddTextarea"
        class="list__add-input q-mb-sm"
        dense
        outlined
      />
      <q-btn @click="createList" label="Добавить список" color="primary" class="q-mr-sm" no-caps />
      <q-btn @click="closeAddForm" icon="close" color="danger" size="md" flat round dense />
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

import TaskManagerPageSkeleton from 'src/pages/client/TaskManager/TaskManagerPageSkeleton.vue'
import AppTaskList from 'src/components/client/TaskManager/TaskList/AppTaskList.vue'

interface Comment {
  id: string
  user_name: string
  content: string
  created_at: string
}

interface Task {
  id: string
  title: string
  content?: string
  completed: boolean
  comments?: Comment[]
}

interface TaskList {
  id: string
  title: string
  tasks?: Task[]
}

interface ResponseTaskShort {
  id: string
  type: string
}

interface ResponseTaskList {
  type: string
  id: string
  attributes: {
    title: string
    created_at: string
  }
  relationships: {
    tasks: {
      data: ResponseTaskShort[],
      meta: {
        tasks_count: bigint
      }
    }
  }
}

interface RelationshipData {
  type: string;
  id: string;
}

interface Relationships {
  [key: string]: {
    data: RelationshipData[]
  }
}

interface IncludedItem {
  type: string
  id: string
  attributes: object
  relationships?: Relationships
}

interface GetListApiResponse {
  data: ResponseTaskList[]
  attributes: {
    title: string
    created_at: string
  }
  included?: IncludedItem[]
  meta: {
    task_lists_count: number
    message?: string
  }
}

interface CreateListApiResponse {
  data: {
    id: string
    attributes: {
      title: string
      created_at: string
    }
    included?: IncludedItem[]
    meta: {
      task_lists_count: number
      message?: string
    }
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

const createList = async () => {
  await api.post<CreateListApiResponse>('v1/task-manager/task-lists', {
    title: model.value.newListName
  }).then(response => {
    $q.notify({
      type: 'positive',
      message: response.data.data.meta.message || 'Список успешно добавлен!'
    })

    const newTaskList: TaskList = {
      id: response.data.data.id,
      title: response.data.data.attributes.title,
      tasks: []
    }

    taskLists.value.push(newTaskList)
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
  await api.get<GetListApiResponse>('v1/task-manager/task-lists').then(response => {
    taskLists.value = response.data.data.map((responseTaskList: ResponseTaskList) => {
      return {
        id: responseTaskList.id,
        title: responseTaskList.attributes.title,
        tasks: getIncluded('tasks.comments', responseTaskList.relationships, response.data.included || [])
      } as TaskList
    })
    listsCount.value = response.data.meta.task_lists_count
  }).catch(error => {
    $q.notify({
      type: 'negative',
      message: (error as AxiosError).message || 'Не удалось загрузить списки задач'
    })
  }).finally(() => {
    loading.value = false
  })
}

const getIncluded = (
  chain: string,
  relationships: Relationships,
  included: IncludedItem[]
) => {
  const chainArray = chain.split('.')
  const initRelationName = chainArray[0]

  const firstLevelRelations = relationships[initRelationName]

  if (!firstLevelRelations || !firstLevelRelations.data) {
    return []
  }

  return firstLevelRelations.data.map(item => {
    const includedItem = included.find((include: IncludedItem) => include.type === initRelationName && include.id === item.id)

    if (!includedItem) {
      return null
    }

    const groupedItem: {
      id: string
      [key: string]: any
    } = {
      id: includedItem.id,
      ...includedItem.attributes
    }

    if (chainArray.length > 1) {
      const remainingRelations = chainArray.slice(1).join('.')
      if (includedItem.relationships) {
        groupedItem[chainArray[1]] = getIncluded(remainingRelations, includedItem.relationships, included)
      }
    }

    return groupedItem
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
