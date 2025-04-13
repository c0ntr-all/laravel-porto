<template>
  <q-card-section class="checklist">
    <div class="text-h6 q-mb-sm">Progress</div>
    <q-list bordered>
      <TMTaskProgressItem
        v-for="(progressItem, key) in progress"
        :key="progressItem.id"
        :progress-item="progressItem"
        :isLast="isProgressItemLast(key)"
      />
    </q-list>
  </q-card-section>
</template>

<script lang="ts" setup>
import { ITask, IProgressItem } from 'src/components/client/TaskManager/types'
import TMTaskProgressItem from 'src/components/client/TaskManager/TMTaskProgressItem.vue'

const props = defineProps<{
  progress: IProgressItem[]
  task: ITask
}>()

const isProgressItemLast = (key: number) => {
  return key === props.progress.length - 1
}
</script>

<style lang="scss" scoped>

</style>
