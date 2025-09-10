<template>
  <div class="reminders">
    <div class="text-h6 q-mb-sm">Event date</div>
    <TMReminderItem :reminder="reminder" />
  </div>
</template>

<script lang="ts" setup>
import { IReminderItem } from 'src/types/TaskManager/task'
import TMReminderItem from 'src/components/client/TaskManager/TMReminderItem.vue'

defineProps<{
  reminder: IReminderItem
}>()
</script>

<style lang="scss" scoped>

</style>
