<template>
  <q-btn
    label="Add checklist"
    color="secondary"
    dense
    unelevated
  >
    <q-menu>
      <div class="row no-wrap q-pa-md">
        <div style="width: 250px">
          <div class="text-h6 q-mb-md">Adding checklist</div>
          <div class="flex column q-gutter-sm">
            <q-input
              v-model="newChecklistTitle"
              label="Checklist name"
              outlined
              dense
            />

            <q-btn
              @click="createChecklist"
              class="q-mb-xs"
              label="Add"
              color="primary"
              unelevated
            />
          </div>
        </div>
      </div>
    </q-menu>
  </q-btn>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useTaskStore } from 'src/stores/modules/taskStore'

// --- Store ---
const taskStore = useTaskStore()

const props = defineProps<{
  taskId: string,
}>()

const newChecklistTitle = ref('Check list')

const createChecklist = () => {
  taskStore.createChecklist(props.taskId, {
    title: newChecklistTitle.value
  })
}
</script>

<style scoped lang="scss">

</style>
