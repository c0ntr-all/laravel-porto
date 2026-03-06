<template>
  <q-card>
    <q-card-section class="flex justify-between">
      <span>{{ user.name }}</span>
      <time class="time">{{ log.created_at }}</time>
    </q-card-section>
    <q-card-section>
      {{ log.event_type }}
    </q-card-section>
  </q-card>
</template>

<script lang="ts" setup>
import { computed } from 'vue'
import { IUseCaseLog } from 'src/types'
import { useTaskStore } from 'src/stores/modules/taskStore'

const taskStore = useTaskStore()

const props = defineProps<{
  log: IUseCaseLog
}>()

const user = computed(() => taskStore.users.byId[props.log.userId])
</script>

<style lang="scss" scoped>

</style>
