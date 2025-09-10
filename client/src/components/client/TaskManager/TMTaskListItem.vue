<template>
  <div class="text task-item">
    <div
      class="task-item__link"
      :class="{
        'task-item__link--finished': isFinished,
        'task-item__link--declined': isDeclined
      }"
      @click.prevent="openTask"
    >
      <div class="task-item__title">
        {{ task.title }}
      </div>
      <TMTaskListItemPanel :task="task" />
    </div>
    <TMTaskListItemActionsButton
      :data="{ isFinished, isDeclined }"
      @finish-switched="switchTaskFinishing"
      @declanation-switched="switchTaskDeclanation"
      @deleted="deleteTask"
    />
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import TMTaskListItemActionsButton from 'src/components/client/TaskManager/TMTaskListItemActionsButton.vue'
import { ITask } from 'src/types/TaskManager/task'
import { useTaskApiRequests } from 'src/composables/client/TaskManager/useTaskApiRequests'
import TMTaskListItemPanel from 'src/components/client/TaskManager/TMTaskListItemPanel.vue'

const props = defineProps<{
  task: ITask
}>()
const emit = defineEmits<{
  (e: 'opened', task: ITask): void
}>()

const {
  task,
  switchTaskFinishing,
  switchTaskDeclanation,
  deleteTask
} = useTaskApiRequests(props.task)

const isFinished = computed(() => task.value.finished_at !== null)
const isDeclined = computed(() => task.value.is_declined === true)

const openTask = () => emit('opened', task.value)
</script>

<style scoped lang="scss">
.task-item {
  position: relative;
  word-break: break-all;
  margin-bottom: 8px;

  &__link {
    display: block;
    padding: 8px;
    background-color: #fff;
    text-decoration: none;
    color: #000;
    border-radius: 3px;
    box-shadow: 0 1px 0 #091e4240;

    &:hover {
      cursor: pointer;
      background-color: #f4f5f7;
      border-bottom-color: #091e4240;
    }

    &--finished {
      background-color: #c9ebbf;

      &:hover {
        background-color: #b0cfa7;
      }
    }

    &--declined {
      background-color: #ebbfbf;

      &:hover {
        background-color: #cfa7a7;
      }
    }
  }
  &__title {

  }
}

.text {
  font-size: 14px;
}
</style>
