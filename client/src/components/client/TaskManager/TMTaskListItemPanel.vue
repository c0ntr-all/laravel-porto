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
import { computed, ref } from 'vue'
import { ITask } from 'src/components/client/TaskManager/types'
import { checkRelationExists } from 'src/utils/helpers'

const props = defineProps<{
  task: ITask
}>()
const task = ref(props.task)
const isChecklistsExists = computed(() => checkRelationExists(task.value, 'checklists'))
const isReminderExists = computed(() => checkRelationExists(task.value, 'reminder'))
const isProgressExists = computed(() => checkRelationExists(task.value, 'progress'))
</script>

<style lang="scss" scoped>
.task-item {
  &__panel {
    display: flex;
    column-gap: 3px;
  }
}
</style>
