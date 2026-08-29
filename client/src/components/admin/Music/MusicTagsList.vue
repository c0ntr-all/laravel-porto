<template>
  <q-tree
    :nodes="nodes"
    node-key="id"
    :filter="filter"
    :filter-method="filterMethod"
    children-key="tags"
  >
    <template v-slot:default-header="scope">
      <div class="flex items-center">
        <div>
          {{ scope.node.name }}
          <q-badge
            v-if="!scope.node.is_active"
            class="q-ml-xs"
            color="grey"
          >
            Inactive
          </q-badge>
        </div>
        <div class="flex q-ml-xs">
          <q-btn
            icon="add"
            size="xs"
            dense
            flat
            rounded
            @click.stop="emit('create-child', scope.node)"
          >
            <q-tooltip>Add sub-tag</q-tooltip>
          </q-btn>
          <q-btn
            icon="edit"
            size="xs"
            color="primary"
            dense
            flat
            rounded
            @click.stop="emit('edit', scope.node)"
          />
          <q-btn
            icon="delete"
            size="xs"
            color="primary"
            dense
            flat
            rounded
            @click.stop="emit('delete', scope.node)"
          />
        </div>
      </div>
    </template>
  </q-tree>
</template>

<script lang="ts" setup>
import { IMusicTag } from 'src/types'

defineProps<{
  nodes: IMusicTag[]
  filter?: string
}>()

const emit = defineEmits<{
  'create-child': [tag: IMusicTag]
  edit: [tag: IMusicTag]
  delete: [tag: IMusicTag]
}>()

const filterMethod = (node: IMusicTag, text: string) => (
  node.name.toLowerCase().includes(text.toLowerCase())
    || node.slug.toLowerCase().includes(text.toLowerCase())
)
</script>
