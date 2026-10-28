<template>
  <q-card class="q-mb-md" flat>
    <q-tabs
      align="left"
      no-caps
      outside-arrows
      mobile-arrows
    >
      <q-route-tab
        :to="{ name: 'task-manager-tasks' }"
        label="Задачи"
      />
      <q-route-tab
        exact
        :to="{ name: 'task-manager-calendar' }"
        label="Календарь"
      />
      <q-route-tab
        exact
        :to="{ name: 'task-manager-templates' }"
        label="Шаблоны"
      />
    </q-tabs>
  </q-card>

  <router-view />

  <q-dialog v-model="isDialogOpen">
    <TMTask
      v-if="selectedTaskId"
      :task-id="selectedTaskId"
      @closed="closeTask"
    />
  </q-dialog>
</template>

<script lang="ts" setup>
import TMTask from 'src/components/client/TaskManager/TMTask.vue'
import { useTaskDialogRoute } from 'src/composables/client/TaskManager/useTaskDialogRoute'

const { selectedTaskId, isDialogOpen, closeTask } = useTaskDialogRoute()
</script>

<style lang="scss" scoped>
</style>
