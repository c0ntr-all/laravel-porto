<template>
  <TaskManagerPageSkeleton v-if="loading"/>
  <template v-else>
    <q-btn
      v-if="!showAddForm"
      @click="openAddForm"
      color="primary"
      icon="add"
      label="Create a list"
      class="q-mb-lg"
      no-caps
    />
    <p>Total lists: {{ listsCount }}</p>
    <div v-if="showAddForm" class="list__add-form q-mb-lg">
      <q-input
        @keyup.enter="createList"
        v-model="model.newListName"
        ref="listAddTextarea"
        class="list__add-input q-mb-sm"
        dense
        outlined
      />
      <q-btn @click="createList" label="Create a list" color="primary" class="q-mr-sm" no-caps/>
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
import { api } from 'src/boot/axios'
import { handleApiError, normalizeApiResponse } from 'src/utils/jsonapi'
import TaskManagerPageSkeleton from 'src/pages/client/TaskManager/TaskManagerPageSkeleton.vue'
import AppTaskList from 'src/components/client/TaskManager/TMTaskList.vue'
import { ITaskList } from 'src/components/client/TaskManager/types'

interface IResponseTaskList {
  type: string
  id: string
  attributes: {
    title: string
    created_at: string
  }
  relationships: {
    tasks: {
      data: [],
      meta: {
        tasks_count: bigint
      }
    }
  }
}

interface IRelationshipData {
  type: string
  id: string
  attributes: object
}

interface IRelationships {
  [key: string]: {
    data: IRelationshipData[]
  }
}

interface IncludedItem {
  type: string
  id: string
  attributes: object
  relationships?: IRelationships
}

interface GetListApiResponse {
  data: IResponseTaskList[]
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
  }
  included?: IncludedItem[]
  meta: {
    task_lists_count: number
    message?: string
  }
}

const $q = useQuasar()

const showAddForm = ref<boolean>(false)
const taskLists = ref<ITaskList[]>([])
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

const createList = async (): Promise<void> => {
  await api.post<CreateListApiResponse>('v1/task-manager/task-lists', {
    title: model.value.newListName
  }).then(response => {
    $q.notify({
      type: 'positive',
      message: response.data.meta.message || 'Task list successfully created!'
    })

    const newTaskList: ITaskList = {
      id: response.data.data.id,
      title: response.data.data.attributes.title,
      relationships: {
        tasks: {
          data: []
        }
      }
    }

    taskLists.value.push(newTaskList)
  }).catch(error => {
    handleApiError(error)
  })
}

const getTaskLists = async (): Promise<void> => {
  loading.value = true
  await api.get<GetListApiResponse>('v1/task-manager/task-lists')
    .then(response => {
      const normalizedResponse = normalizeApiResponse(response.data)

      taskLists.value = normalizedResponse.data
      listsCount.value = response.data.meta.count
    }).catch(error => {
      handleApiError(error)
    }).finally(() => {
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
