<template>
  <div class="reminder-log">
    <q-btn
      class="reminder-log__toggle"
      :icon="isExpanded ? 'expand_less' : 'expand_more'"
      :label="toggleLabel"
      color="grey-8"
      flat
      dense
      no-caps
      @click="isExpanded = !isExpanded"
    />

    <q-slide-transition>
      <div v-show="isExpanded" class="reminder-log__list">
        <p
          v-if="!isLoading && !occurrences.length"
          class="reminder-log__empty"
        >
          Отметок пока нет
        </p>
        <TMReminderOccurrenceItem
          v-for="occurrence in occurrences"
          :key="occurrence.id"
          :occurrence="occurrence"
        />
      </div>
    </q-slide-transition>

    <q-inner-loading :showing="isLoading">
      <q-spinner size="24px" color="primary" />
    </q-inner-loading>
  </div>
</template>

<script lang="ts" setup>
import { computed, onMounted, ref, watch } from 'vue'
import { IReminderOccurrence } from 'src/types/TaskManager/task'
import { useTaskStore } from 'src/stores/modules/taskStore'
import TMReminderOccurrenceItem from 'src/components/client/TaskManager/TMReminderOccurrenceItem.vue'

const taskStore = useTaskStore()

const props = defineProps<{
  taskId: string
  reminderId: string
}>()

const isLoading = ref(true)
const isExpanded = ref(false)

const occurrences = computed<IReminderOccurrence[]>(() =>
  taskStore.reminderOccurrences.allIds
    .map(id => taskStore.reminderOccurrences.byId[id])
    .filter((item): item is IReminderOccurrence =>
      Boolean(item) && item.reminder_id === props.reminderId
    )
    .sort((a, b) => parseTime(b.scheduled_at) - parseTime(a.scheduled_at))
)

const completedCount = computed(() =>
  occurrences.value.filter(item => item.status === 'completed').length
)

const toggleLabel = computed(() => {
  if (!occurrences.value.length) return 'История отметок'
  return `История отметок · ${completedCount.value}`
})

function parseTime(value: string): number {
  const parsed = new Date(value.replace(' ', 'T'))
  return Number.isNaN(parsed.getTime()) ? 0 : parsed.getTime()
}

async function loadOccurrences() {
  isLoading.value = true
  try {
    await taskStore.getReminderOccurrences(props.taskId)
    if (occurrences.value.length) {
      isExpanded.value = true
    }
  } finally {
    isLoading.value = false
  }
}

watch(() => occurrences.value.length, (count, previousCount) => {
  if (count > (previousCount || 0)) {
    isExpanded.value = true
  }
})

watch(() => props.reminderId, () => {
  loadOccurrences()
})

onMounted(() => {
  loadOccurrences()
})
</script>

<style lang="scss" scoped>
.reminder-log {
  position: relative;
  margin-top: 8px;

  &__toggle {
    padding-left: 0;
  }

  &__list {
    padding: 0 2px 4px;
  }

  &__empty {
    margin: 4px 0 0;
    color: #9e9e9e;
    font-size: 13px;
  }
}
</style>
