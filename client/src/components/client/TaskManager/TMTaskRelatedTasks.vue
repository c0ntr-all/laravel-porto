<template>
  <div class="related-tasks">
    <div class="text-h6 q-mb-sm">Связанные задачи</div>
    <q-list bordered separator>
      <q-item
        v-for="item in relatedTasks"
        :key="`${item.relationType}-${item.id}`"
        class="related-tasks__item"
        clickable
        @click.stop="openRelatedTask(item.id)"
      >
        <q-item-section>
          <q-item-label>{{ item.title }}</q-item-label>
          <q-item-label caption>
            {{ relationLabel(item.relationType) }}
          </q-item-label>
        </q-item-section>
        <q-item-section side>
          <q-icon name="chevron_right" color="grey-6" />
        </q-item-section>
      </q-item>
    </q-list>
  </div>
</template>

<script lang="ts" setup>
import { computed } from 'vue'
import { useTaskStore } from 'src/stores/modules/taskStore'
import { useTaskDialogRoute } from 'src/composables/client/TaskManager/useTaskDialogRoute'
import { IRelatedTaskItem, ITask, TaskRelationType } from 'src/types/TaskManager/task'

const props = defineProps<{
  task: ITask
}>()

const taskStore = useTaskStore()
const { openTask } = useTaskDialogRoute()

const relatedTasks = computed<IRelatedTaskItem[]>(() => {
  const items: IRelatedTaskItem[] = []

  if (props.task.parent_id) {
    const parent = taskStore.tasks.byId[props.task.parent_id]
    if (parent) {
      items.push({
        id: parent.id,
        title: parent.title,
        relationType: 'parent'
      })
    }
  }

  for (const subtaskId of props.task.subtasksIds || []) {
    const subtask = taskStore.tasks.byId[subtaskId]
    if (!subtask) continue

    items.push({
      id: subtask.id,
      title: subtask.title,
      relationType: 'subtask'
    })
  }

  return items
})

const relationLabel = (relationType: TaskRelationType) => {
  return relationType === 'parent' ? 'Родительская' : 'Подзадача'
}

const openRelatedTask = (taskId: string) => {
  openTask(taskId)
}
</script>

<style lang="scss" scoped>
.related-tasks {
  &__item {
    transition: background-color 0.15s ease;
  }
}
</style>
