<template>
  <q-card class="q-mb-md" flat>
    <q-tabs
      v-model="tab"
      align="left"
      no-caps
      outside-arrows
      mobile-arrows
    >
      <q-tab name="tasks" label="Задачи" />
      <q-tab name="calendar" label="Календарь" />
      <q-tab name="templates" label="Шаблоны" />
    </q-tabs>
  </q-card>

  <q-tab-panels
    v-model="tab"
    animated
    transition-prev="fade"
    transition-next="fade"
  >
    <q-tab-panel name="tasks" class="q-pa-none">
      <TMTasksTab />
    </q-tab-panel>

    <q-tab-panel name="calendar" class="q-pa-none">
      <TMCalendarTab />
    </q-tab-panel>

    <q-tab-panel name="templates" class="q-pa-none">
      <TMTaskTemplatesTab />
    </q-tab-panel>
  </q-tab-panels>

  <q-dialog v-model="isDialogOpen">
    <TMTask
      v-if="selectedTaskId"
      :task-id="selectedTaskId"
      @closed="closeTask"
    />
  </q-dialog>
</template>

<script lang="ts" setup>
import { ref, watch } from 'vue'
import TMTasksTab from 'src/components/client/TaskManager/TMTasksTab.vue'
import TMCalendarTab from 'src/components/client/TaskManager/TMCalendarTab.vue'
import TMTaskTemplatesTab from 'src/components/client/TaskManager/TMTaskTemplatesTab.vue'
import TMTask from 'src/components/client/TaskManager/TMTask.vue'
import { useTaskDialogRoute } from 'src/composables/client/TaskManager/useTaskDialogRoute'

const tab = ref<'tasks' | 'calendar' | 'templates'>('tasks')
const { selectedTaskId, isDialogOpen, closeTask } = useTaskDialogRoute()

watch(selectedTaskId, (taskId) => {
  // Keep calendar tab when opening a task from the calendar view
  if (taskId && tab.value === 'templates') {
    tab.value = 'tasks'
  }
}, { immediate: true })
</script>

<style lang="scss" scoped>
.q-tab-panels {
  background-color: transparent;
}
</style>
