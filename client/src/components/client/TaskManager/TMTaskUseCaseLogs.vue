<template>
  <div class="history">
    <div v-if="history.length" class="comments-list column q-gutter-sm">
      <TMTaskUseCaseLog
        v-for="log in history"
        :key="log.id"
        :log="log"
      />
    </div>
    <p v-else class="text-grey-5">There are no history!</p>
    <q-inner-loading :showing="isCommentsLoading">
      <q-spinner-gears size="50px" color="primary" />
    </q-inner-loading>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, onMounted } from 'vue'
import { useTaskStore } from 'src/stores/modules/taskStore'
import { IUseCaseLog } from 'src/types'
import TMTaskUseCaseLog from 'src/components/client/TaskManager/TMTaskUseCaseLog.vue'

const taskStore = useTaskStore()

const props = defineProps<{
  taskId: string,
}>()

const isHistoryLoading = ref(true)
const history = computed<IUseCaseLog[]>(() => {
  // TODO: переделать под Enum
  return Object.values(taskStore.useCaseLogs.byId).filter(log => {
    return log.loggable_id === props.taskId && log.loggable_type === 'tm_tasks'
  })
})
async function loadHistory() {
  await taskStore.getUseCaseLogs({
    loggable_id: props.taskId,
    // TODO: переделать под Enum
    loggable_type: 'tm_tasks'
  }).finally(() => {
    isHistoryLoading.value = false
  })
}

onMounted(() => {
  loadHistory()
})
</script>

<style scoped lang="scss">

</style>
