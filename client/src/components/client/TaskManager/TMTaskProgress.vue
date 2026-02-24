<template>
  <div class="progress">
    <div class="text-h6 q-mb-sm">Progress</div>
    <q-list bordered>
      <TMTaskProgressItem
        v-for="(progressItem, key) in progress"
        :key="progressItem.id"
        :progress-item="progressItem"
        :isLast="isProgressItemLast(key)"
      />
    </q-list>
  </div>
</template>

<script lang="ts" setup>
import { ITask, IProgress } from 'src/types/TaskManager/task'
import TMTaskProgressItem from 'src/components/client/TaskManager/TMTaskProgressItem.vue'

const props = defineProps<{
  progress: IProgress[]
  task: ITask
}>()

const isProgressItemLast = (key: number) => {
  return key === props.progress.length - 1
}
</script>

<style lang="scss" scoped>

</style>
