<template>
  <div class="dashboard-toolbar">
    <q-select
      :model-value="currentId"
      :options="options"
      emit-value
      map-options
      dense
      outlined
      class="dashboard-toolbar__select"
      @update:model-value="emit('select', $event)"
    />

    <q-btn
      unelevated
      color="primary"
      icon="add"
      label="Виджет"
      :disable="!currentId"
      @click="emit('add-widget')"
    />
    <q-btn
      outline
      color="primary"
      icon="dashboard_customize"
      label="Дашборд"
      @click="emit('create-dashboard')"
    />
    <q-btn
      :outline="!editing"
      :unelevated="editing"
      color="primary"
      :icon="editing ? 'done' : 'edit'"
      :label="editing ? 'Готово' : 'Изменить'"
      @click="emit('toggle-edit')"
    />

    <q-btn-dropdown v-if="currentId" unelevated color="grey-3" text-color="grey-8" icon="more_vert" dense>
      <q-list>
        <q-item v-close-popup clickable @click="emit('rename')">
          <q-item-section>Переименовать</q-item-section>
        </q-item>
        <q-item v-close-popup clickable :disable="isDefault" @click="emit('set-default')">
          <q-item-section>Сделать основным</q-item-section>
        </q-item>
        <q-item v-close-popup clickable @click="emit('remove')">
          <q-item-section class="text-negative">Удалить дашборд</q-item-section>
        </q-item>
      </q-list>
    </q-btn-dropdown>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { IDashboard } from 'src/types/Dashboard/dashboard'

const props = defineProps<{
  dashboards: IDashboard[]
  currentId: string | null
  editing: boolean
  isDefault: boolean
}>()

const emit = defineEmits<{
  select: [id: string]
  'add-widget': []
  'create-dashboard': []
  'toggle-edit': []
  rename: []
  'set-default': []
  remove: []
}>()

const options = computed(() => props.dashboards.map(item => ({
  label: item.is_default ? `${item.name} · основной` : item.name,
  value: item.id
})))
</script>

<style lang="scss" scoped>
.dashboard-toolbar {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 8px;
  margin-bottom: 16px;

  &__select {
    min-width: 220px;
  }
}
</style>
