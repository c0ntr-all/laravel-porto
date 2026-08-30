<template>
  <div class="reminders">
    <div class="text-h6 q-mb-sm">Напоминание</div>
    <TMReminderItem
      :reminder="reminder"
      :task-id="taskId"
    />
    <TMReminderOccurrences
      :task-id="taskId"
      :reminder-id="reminder.id"
    />
  </div>
</template>

<script lang="ts" setup>
import { IReminderItem } from 'src/types/TaskManager/task'
import TMReminderItem from 'src/components/client/TaskManager/TMReminderItem.vue'
import TMReminderOccurrences from 'src/components/client/TaskManager/TMReminderOccurrences.vue'

defineProps<{
  reminder: IReminderItem
  taskId: string
}>()
</script>

<style lang="scss" scoped>

</style>
