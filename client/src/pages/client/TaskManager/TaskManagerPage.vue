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
        @keyup.enter="createTaskList"
        v-model="model.newListName"
        ref="listAddTextarea"
        class="list__add-input q-mb-sm"
        dense
        outlined
      />
      <q-btn @click="createTaskList" label="Create a list" color="primary" class="q-mr-sm" no-caps/>
      <q-btn @click="closeAddForm" icon="close" color="danger" size="md" flat round dense/>
    </div>
    <div class="task-lists row items-start q-gutter-md q-mb-lg">
      <TMTaskList
        v-for="list in listsWithTasks"
        :list="list"
        :key="list.id"
        :ref="'list-ref-' + list.id"
      />
    </div>
  </template>
</template>

<script lang="ts" setup>
import { ref, computed, onMounted, nextTick } from 'vue'
import { handleApiError } from 'src/utils/jsonapi'
import TaskManagerPageSkeleton from 'src/pages/client/TaskManager/TaskManagerPageSkeleton.vue'
import TMTaskList from 'src/components/client/TaskManager/TMTaskList.vue'
import { useTaskStore } from 'src/stores/modules/taskStore'

// --- Store ---
const taskStore = useTaskStore()

// --- State ---
const showAddForm = ref<boolean>(false)
const listsCount = ref<number>(0)
const loading = ref<boolean>(true)
const listAddTextarea = ref<HTMLElement | null>(null)
const model = ref<{ newListName: string }>({
  newListName: ''
})

// --- Computed ---
const listsWithTasks = computed(() =>
  taskStore.taskLists.allIds.map(listId => taskStore.taskLists.byId[listId])
)

// --- Methods ---
const openAddForm = () => {
  showAddForm.value = true
  nextTick(() => {
    listAddTextarea.value?.focus()
  })
}

const closeAddForm = () => {
  showAddForm.value = false
}

const getTaskLists = async (): Promise<void> => {
  await taskStore.getTaskLists()
    .catch(error => {
      handleApiError(error)
    })
    .finally(() => {
      loading.value = false
    })
}

const createTaskList = async (): Promise<void> => {
  await taskStore.createTaskList({ title: model.value.newListName })
    .catch(error => {
      handleApiError(error)
    })
    .finally(() => {
      clearModel()
    })
}

const clearModel = () => {
  model.value.newListName = ''
}

// --- Hooks ---
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
