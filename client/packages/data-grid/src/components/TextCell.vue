<template>
  <input
    class="hp-data-grid__input"
    :value="displayValue"
    :readonly="readonly"
    :aria-label="column.title"
    @input="onInput"
  />
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { CellValue, DataGridColumn } from '../types'

const props = defineProps<{
  value: CellValue
  column: DataGridColumn
  readonly?: boolean
}>()

const emit = defineEmits<{
  update: [value: CellValue]
}>()

const displayValue = computed(() => (props.value == null ? '' : String(props.value)))

function onInput(event: Event) {
  const target = event.target as HTMLInputElement
  emit('update', target.value)
}
</script>
