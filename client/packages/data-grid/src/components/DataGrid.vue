<template>
  <div class="hp-data-grid" :class="{ 'hp-data-grid--readonly': readonly }">
    <div class="hp-data-grid__scroll">
      <table class="hp-data-grid__table">
        <thead>
          <tr>
            <DataGridHeaderCell
              v-for="column in columns"
              :key="column.id"
              :column="column"
              :readonly="readonly"
              :can-delete="columns.length > 1"
              :delete-label="resolvedLabels.deleteColumn"
              @rename="renameColumn(column.id, $event)"
              @remove="removeColumn(column.id)"
            />
            <th v-if="!readonly" class="hp-data-grid__th hp-data-grid__th--action">
              <button
                type="button"
                class="hp-data-grid__add-btn"
                :aria-label="resolvedLabels.addColumn"
                :title="resolvedLabels.addColumn"
                @click="onAddColumn"
              >
                <svg viewBox="0 0 16 16" aria-hidden="true">
                  <path d="M8 3v10M3 8h10" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                </svg>
              </button>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="row in rows" :key="row.id" class="hp-data-grid__row">
            <td v-for="column in columns" :key="column.id" class="hp-data-grid__td">
              <component
                :is="resolveCell(column.type)"
                :value="getCellValue(row, column.id)"
                :column="column"
                :row="row"
                :readonly="readonly"
                @update="setCellValue(row.id, column.id, $event)"
              />
            </td>
            <td v-if="!readonly" class="hp-data-grid__td hp-data-grid__td--action">
              <button
                type="button"
                class="hp-data-grid__icon-btn hp-data-grid__icon-btn--danger hp-data-grid__row-remove"
                :aria-label="resolvedLabels.deleteRow"
                :title="resolvedLabels.deleteRow"
                @click="removeRow(row.id)"
              >
                <svg viewBox="0 0 16 16" aria-hidden="true">
                  <path d="M4 4l8 8M12 4l-8 8" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                </svg>
              </button>
            </td>
          </tr>
        </tbody>
        <tfoot v-if="!readonly">
          <tr>
            <td class="hp-data-grid__td hp-data-grid__td--footer" :colspan="columns.length + 1">
              <button
                type="button"
                class="hp-data-grid__add-row"
                @click="addRow()"
              >
                <svg viewBox="0 0 16 16" aria-hidden="true">
                  <path d="M8 3v10M3 8h10" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                </svg>
                <span>{{ resolvedLabels.addRow }}</span>
              </button>
            </td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, toRef, watch } from 'vue'
import { cloneJson } from '../clone'
import { useDataGrid } from '../composables/useDataGrid'
import { getCellValue } from '../document'
import '../registerBuiltins'
import { cellRendererRegistry } from '../registries/cellRenderers'
import { defaultDataGridLabels, type DataGridDocument, type DataGridLabels } from '../types'
import DataGridHeaderCell from './DataGridHeaderCell.vue'
import '../styles/data-grid.css'

const props = withDefaults(defineProps<{
  modelValue?: DataGridDocument
  readonly?: boolean
  labels?: Partial<DataGridLabels>
}>(), {
  readonly: false
})

const emit = defineEmits<{
  'update:modelValue': [value: DataGridDocument]
  change: [value: DataGridDocument]
}>()

const resolvedLabels = computed(() => ({
  ...defaultDataGridLabels,
  ...props.labels
}))

const model = toRef(props, 'modelValue')
const grid = useDataGrid(model, {
  untitledColumn: resolvedLabels.value.untitledColumn
})

const {
  columns,
  rows,
  addColumn,
  renameColumn,
  removeColumn,
  addRow,
  removeRow,
  setCellValue
} = grid

watch(() => grid.document.value, (value) => {
  const next = cloneJson(value)
  emit('update:modelValue', next)
  emit('change', next)
}, { deep: true })

function onAddColumn() {
  addColumn(`${resolvedLabels.value.untitledColumn} ${columns.value.length + 1}`)
}

function resolveCell(type: string) {
  return cellRendererRegistry.resolve(type).component
}

defineExpose({
  addColumn,
  addRow,
  load: grid.load,
  reset: grid.reset,
  toJSON: grid.toJSON,
  toJSONString: grid.toJSONString
})
</script>
