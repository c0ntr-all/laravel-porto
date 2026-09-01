<template>
  <TaskManagerPageSkeleton v-if="loading"/>
  <template v-else>
    <div class="tasks-tab__toolbar q-mb-lg">
      <div class="tasks-tab__toolbar-main">
        <q-btn
          v-if="!showAddForm && viewMode === TasksViewModeEnum.BLOCKS"
          @click="openAddForm"
          color="primary"
          icon="add"
          label="Create a list"
          no-caps
        />
        <div v-if="showAddForm" class="list__add-form">
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
        <p class="tasks-tab__count q-ma-none">{{ countLabel }}</p>
      </div>

      <q-btn-toggle
        v-model="viewMode"
        unelevated
        no-caps
        dense
        toggle-color="primary"
        color="white"
        text-color="grey-8"
        :options="viewModeOptions"
      />
    </div>

    <div v-if="viewMode === TasksViewModeEnum.BLOCKS" class="task-lists row items-start q-gutter-md q-mb-lg">
      <TMTaskList
        v-for="list in listsWithTasks"
        :list="list"
        :key="list.id"
        :ref="'list-ref-' + list.id"
      />
    </div>

    <TMTasksListView v-else />
  </template>
</template>

<script lang="ts" setup>
import { ref, computed, onMounted, nextTick, watch } from 'vue'
import { handleApiError } from 'src/utils/jsonapi'
import TaskManagerPageSkeleton from 'src/pages/client/TaskManager/TaskManagerPageSkeleton.vue'
import TMTaskList from 'src/components/client/TaskManager/TMTaskList.vue'
import TMTasksListView from 'src/components/client/TaskManager/TMTasksListView.vue'
import { useTaskStore } from 'src/stores/modules/taskStore'
import { useSettingsStore } from 'src/stores/modules/settingsStore'
import { TasksViewModeEnum } from 'src/enums/TaskManager/TasksViewModeEnum'

const taskStore = useTaskStore()
const settingsStore = useSettingsStore()

const showAddForm = ref<boolean>(false)
const loading = ref<boolean>(true)
const listAddTextarea = ref<HTMLElement | null>(null)
const model = ref<{ newListName: string }>({
  newListName: ''
})
const viewMode = computed({
  get: () => settingsStore.settings.taskManager.defaultViewMode,
  set: (mode: TasksViewModeEnum) => {
    settingsStore.updateTaskManager({ defaultViewMode: mode })
  }
})

const viewModeOptions = [
  { label: 'Блоки', value: TasksViewModeEnum.BLOCKS, icon: 'view_column' },
  { label: 'Список', value: TasksViewModeEnum.LIST, icon: 'view_list' }
]

const listsWithTasks = computed(() =>
  taskStore.taskLists.allIds.map(listId => taskStore.taskLists.byId[listId])
)

const listsCount = computed(() => listsWithTasks.value.length)
const tasksCount = computed(() => taskStore.tasks.allIds.length)

const countLabel = computed(() =>
  viewMode.value === TasksViewModeEnum.BLOCKS
    ? `Всего списков: ${listsCount.value}`
    : `Всего задач: ${tasksCount.value}`
)

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

watch(viewMode, (mode) => {
  if (mode === TasksViewModeEnum.LIST) {
    closeAddForm()
  }
})

onMounted(() => {
  getTaskLists()
})
</script>

<style lang="scss" scoped>
.tasks-tab {
  &__toolbar {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
  }

  &__toolbar-main {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
    min-width: 0;
  }

  &__count {
    color: #6b7280;
  }
}

.list {
  &__add-input {
    max-width: 300px;
  }
}
</style>
