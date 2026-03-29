<template>
  <div class="task-item__panel">
    <div v-if="isReminderExists" class="task-item__panel-item">
      <q-icon
        name="schedule"
      />
    </div>
    <div v-if="isChecklistsExists" class="task-item__panel-item">
      <q-icon
        name="checklist"
      />
    </div>
    <div v-if="isProgressExists" class="task-item__panel-item">
      <q-icon
        name="timeline"
      />
    </div>
  </div>
</template>

<script lang="ts" setup>
import { computed } from 'vue'
import { ITask } from 'src/types/TaskManager/task'

const props = defineProps<{
  task: ITask
}>()

const isChecklistsExists = computed(() => props.task.checklists_count > 0)
const isReminderExists = computed(() => props.task.reminders_count > 0)
const isProgressExists = computed(() => props.task.progresses_count > 0)
</script>

<style lang="scss" scoped>
.task-item {
  &__panel {
    display: flex;
    column-gap: 3px;
  }
}
</style>
