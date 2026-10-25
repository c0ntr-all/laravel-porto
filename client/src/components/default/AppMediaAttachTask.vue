<template>
  <q-dialog
    v-model="show"
    persistent
  >
    <q-card class="task-picker">
      <header class="task-picker__header">
        <h2 class="task-picker__title">
          Выбрать задачу
        </h2>
        <q-btn
          flat
          round
          dense
          icon="close"
          aria-label="Закрыть"
          @click="cancel"
        />
      </header>

      <q-separator />

      <div class="task-picker__toolbar">
        <q-input
          v-model="search"
          dense
          outlined
          clearable
          placeholder="Поиск по названию"
        >
          <template #prepend>
            <q-icon name="search" />
          </template>
        </q-input>
      </div>

      <div class="task-picker__body">
        <div v-if="isLoading && !groups.length" class="task-picker__state">
          <q-spinner-dots color="primary" size="36px" />
        </div>

        <div v-else-if="error" class="task-picker__state">
          <q-icon name="cloud_off" size="36px" />
          <div>{{ error }}</div>
          <q-btn
            flat
            no-caps
            color="primary"
            label="Повторить"
            @click="load"
          />
        </div>

        <div v-else-if="!groups.length" class="task-picker__state">
          <q-icon name="task_alt" size="36px" />
          <div>{{ search.trim() ? 'Ничего не найдено' : 'Задач пока нет' }}</div>
        </div>

        <div v-else class="task-picker__groups">
          <section
            v-for="group in groups"
            :key="group.id"
            class="task-group"
          >
            <div class="task-group__title">
              <q-icon name="folder_open" size="16px" />
              <span class="ellipsis">{{ group.title }}</span>
            </div>

            <button
              v-for="task in group.tasks"
              :key="task.id"
              type="button"
              class="task-row"
              :class="{ 'task-row--selected': selectedId === task.id }"
              :aria-pressed="selectedId === task.id"
              @click="select(task, group.title)"
            >
              <span class="task-row__icon">
                <q-icon name="check_box_outline_blank" size="20px" />
              </span>
              <span class="task-row__text">
                <span class="task-row__name ellipsis">{{ task.title }}</span>
                <span class="task-row__list ellipsis">{{ group.title }}</span>
              </span>
              <q-icon
                v-if="selectedId === task.id"
                name="check_circle"
                color="positive"
                size="20px"
              />
            </button>
          </section>
        </div>
      </div>

      <q-separator />

      <footer class="task-picker__footer">
        <q-btn
          flat
          no-caps
          color="grey-8"
          label="Отмена"
          @click="cancel"
        />
        <q-btn
          unelevated
          no-caps
          color="primary"
          label="Выбрать"
          :disable="!selected"
          @click="confirm"
        />
      </footer>
    </q-card>
  </q-dialog>
</template>

<script lang="ts" setup>
import { computed, ref, watch } from 'vue'
import { storeToRefs } from 'pinia'
import { useTaskStore } from 'src/stores/modules/taskStore'
import { IPostTaskSubject } from 'src/types'
import { ITask } from 'src/types/TaskManager/task'
import { handleApiError } from 'src/utils/jsonapi'

const emit = defineEmits<{
  select: [task: IPostTaskSubject]
}>()

const show = defineModel<boolean>({ default: false })

const taskStore = useTaskStore()
const { taskLists, tasks } = storeToRefs(taskStore)

const search = ref('')
const selected = ref<IPostTaskSubject | null>(null)
const isLoading = ref(false)
const error = ref<string | null>(null)
let requestId = 0

const selectedId = computed(() => selected.value?.id ?? null)

const groups = computed(() => {
  const query = search.value.trim().toLowerCase()
  const result: Array<{ id: string, title: string, tasks: ITask[] }> = []

  for (const listId of taskLists.value.allIds) {
    const list = taskLists.value.byId[listId]
    if (!list) {
      continue
    }

    const listTasks = (list.tasksIds ?? [])
      .map(id => tasks.value.byId[id])
      .filter((task): task is ITask => Boolean(task) && !task.finished_at && !task.is_declined)
      .filter(task => {
        if (!query) {
          return true
        }

        return task.title.toLowerCase().includes(query)
      })

    if (!listTasks.length) {
      continue
    }

    result.push({
      id: list.id,
      title: list.title,
      tasks: listTasks
    })
  }

  return result
})

watch(show, isOpen => {
  if (!isOpen) {
    return
  }

  selected.value = null
  search.value = ''
  error.value = null
  void load()
})

async function load () {
  const currentRequest = ++requestId
  isLoading.value = true
  error.value = null

  try {
    await taskStore.getTaskLists()
  } catch (err) {
    if (currentRequest !== requestId) {
      return
    }

    error.value = 'Не удалось загрузить задачи'
    handleApiError(err)
  } finally {
    if (currentRequest === requestId) {
      isLoading.value = false
    }
  }
}

function select (task: ITask, listTitle: string) {
  selected.value = {
    id: task.id,
    title: task.title,
    task_list_id: task.task_list_id,
    task_list_title: listTitle
  }
}

function cancel () {
  show.value = false
}

function confirm () {
  if (!selected.value) {
    return
  }

  show.value = false
  emit('select', selected.value)
}
</script>

<style lang="scss" scoped>
.task-picker {
  display: flex;
  flex-direction: column;
  width: min(560px, 94vw);
  height: min(680px, 88vh);
  max-height: 88vh;
  border-radius: 16px;

  &__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex: none;
    padding: 14px 8px 14px 18px;
  }

  &__title {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 600;
    color: #282f53;
  }

  &__toolbar {
    flex: none;
    padding: 12px 12px 0;
  }

  &__body {
    flex: 1;
    min-height: 0;
    overflow: auto;
    padding: 12px;
  }

  &__state {
    min-height: 240px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
    color: #777a8f;
    text-align: center;
  }

  &__groups {
    display: flex;
    flex-direction: column;
    gap: 14px;
  }

  &__footer {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    flex: none;
    padding: 12px 16px;
  }
}

.task-group {
  &__title {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin: 0 4px 6px;
    color: #777a8f;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
  }
}

.task-row {
  display: flex;
  align-items: center;
  gap: 12px;
  width: 100%;
  padding: 10px 12px;
  margin-bottom: 4px;
  border: 1px solid #e4e7eb;
  border-radius: 12px;
  background: #fff;
  color: inherit;
  font: inherit;
  text-align: left;
  cursor: pointer;

  &:hover {
    border-color: $primary;
    background: rgba(108, 95, 252, 0.04);
  }

  &:focus-visible {
    outline: 2px solid $primary;
    outline-offset: 2px;
  }

  &--selected {
    border-color: $primary;
    background: rgba(108, 95, 252, 0.08);
  }

  &__icon {
    display: flex;
    color: #9aa0b8;
  }

  &__text {
    display: flex;
    flex-direction: column;
    gap: 2px;
    min-width: 0;
    flex: 1;
  }

  &__name {
    font-weight: 600;
    color: #282f53;
  }

  &__list {
    color: #777a8f;
    font-size: 13px;
  }
}
</style>
