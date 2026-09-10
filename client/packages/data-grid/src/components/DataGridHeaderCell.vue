<template>
  <th
    class="hp-data-grid__th"
    scope="col"
    :style="column.width ? { width: `${column.width}px` } : undefined"
  >
    <div class="hp-data-grid__th-inner">
      <input
        v-if="editing"
        ref="inputRef"
        class="hp-data-grid__header-input"
        :value="draft"
        @input="draft = ($event.target as HTMLInputElement).value"
        @blur="commit"
        @keydown.enter.prevent="commit"
        @keydown.esc.prevent="cancel"
      />
      <button
        v-else
        type="button"
        class="hp-data-grid__header-title"
        @click="startEdit"
      >
        {{ column.title }}
      </button>

      <button
        v-if="!readonly && canDelete"
        type="button"
        class="hp-data-grid__icon-btn hp-data-grid__icon-btn--danger"
        :aria-label="deleteLabel"
        :title="deleteLabel"
        @click.stop="$emit('remove')"
      >
        <svg viewBox="0 0 16 16" aria-hidden="true">
          <path d="M4 4l8 8M12 4l-8 8" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
        </svg>
      </button>
    </div>
  </th>
</template>

<script setup lang="ts">
import { nextTick, ref } from 'vue'
import type { DataGridColumn } from '../types'

const props = defineProps<{
  column: DataGridColumn
  readonly?: boolean
  canDelete?: boolean
  deleteLabel?: string
}>()

const emit = defineEmits<{
  rename: [title: string]
  remove: []
}>()

const editing = ref(false)
const draft = ref(props.column.title)
const inputRef = ref<HTMLInputElement | null>(null)

async function startEdit() {
  if (props.readonly) {
    return
  }

  draft.value = props.column.title
  editing.value = true
  await nextTick()
  inputRef.value?.focus()
  inputRef.value?.select()
}

function commit() {
  if (!editing.value) {
    return
  }

  editing.value = false
  emit('rename', draft.value)
}

function cancel() {
  editing.value = false
  draft.value = props.column.title
}
</script>
