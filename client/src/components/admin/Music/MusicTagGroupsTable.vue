<template>
  <q-table
    class="tag-groups-table"
    :rows="store.groups"
    :columns="columns"
    row-key="id"
    :loading="store.isGroupsLoading"
    :pagination="pagination"
    flat
    bordered
    hide-pagination
    no-data-label="No tag groups yet"
  >
    <template #body-cell-is_system="props">
      <q-td :props="props">
        <q-badge v-if="props.row.is_system" color="grey-7">System</q-badge>
        <span v-else>—</span>
      </q-td>
    </template>

    <template #body-cell-is_active="props">
      <q-td :props="props">
        <q-badge :color="props.row.is_active ? 'positive' : 'grey'">
          {{ props.row.is_active ? 'Active' : 'Inactive' }}
        </q-badge>
      </q-td>
    </template>

    <template #body-cell-actions="props">
      <q-td :props="props">
        <q-btn
          icon="edit"
          color="primary"
          flat
          round
          dense
          @click="emit('edit', props.row)"
        />
        <q-btn
          icon="delete"
          color="negative"
          flat
          round
          dense
          :disable="props.row.is_system"
          @click="confirmDelete(props.row)"
        >
          <q-tooltip v-if="props.row.is_system">System groups cannot be deleted</q-tooltip>
        </q-btn>
      </q-td>
    </template>
  </q-table>

  <q-dialog v-model="showDelete">
    <q-card style="min-width: 360px">
      <q-card-section class="text-h6">Delete tag group</q-card-section>
      <q-card-section>
        Delete group <b>{{ groupToDelete?.name }}</b>? Tags in this group will become ungrouped.
      </q-card-section>
      <q-card-actions align="right">
        <q-btn flat label="Cancel" v-close-popup/>
        <q-btn
          color="negative"
          label="Delete"
          :loading="store.isSaving"
          @click="removeGroup"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script lang="ts" setup>
import { ref } from 'vue'
import { QTableColumn } from 'quasar'
import { useMusicTagStore } from 'src/stores/modules/musicTagStore'
import { IMusicTagGroup } from 'src/types'

const emit = defineEmits<{
  edit: [group: IMusicTagGroup]
}>()

const store = useMusicTagStore()
const showDelete = ref(false)
const groupToDelete = ref<IMusicTagGroup | null>(null)
const pagination = { rowsPerPage: 0 }

const columns: QTableColumn<IMusicTagGroup>[] = [
  { name: 'name', label: 'Name', field: 'name', align: 'left' },
  { name: 'slug', label: 'Slug', field: 'slug', align: 'left' },
  {
    name: 'description',
    label: 'Description',
    field: 'description',
    align: 'left',
    format: (value: string | null) => value || '—'
  },
  { name: 'display_order', label: 'Order', field: 'display_order', align: 'right' },
  { name: 'is_system', label: 'System', field: 'is_system', align: 'left' },
  { name: 'is_active', label: 'Status', field: 'is_active', align: 'left' },
  { name: 'actions', label: '', field: 'id', align: 'right' }
]

const confirmDelete = (group: IMusicTagGroup) => {
  groupToDelete.value = group
  showDelete.value = true
}

const removeGroup = async () => {
  if (!groupToDelete.value) {
    return
  }

  const deleted = await store.deleteGroup(groupToDelete.value.id)
  if (deleted) {
    showDelete.value = false
    groupToDelete.value = null
  }
}
</script>
