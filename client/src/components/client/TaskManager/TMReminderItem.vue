<template>
  <q-chip outline square color="grey" text-color="white" icon="alarm" :label="humanDatetime(reminder.datetime)" />
  <span v-if="reminder.interval">Every {{ reminder.interval }}</span>
</template>

<script lang="ts" setup>
import { IReminderItem } from 'src/types/TaskManager/task'
import { humanDatetime } from 'src/utils/datetime'

defineProps<{
  reminder: IReminderItem
}>()
</script>

<style lang="scss" scoped>

</style>
