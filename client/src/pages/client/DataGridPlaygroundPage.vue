<template>
  <div class="data-grid-playground">
    <p class="text-grey-7 q-mb-md">
      Таблица хранится как JSON. Ниже — живой документ, который можно сохранить в поле бэкенда.
    </p>

    <DataGrid
      v-model="document"
      :labels="labels"
    />

    <div class="row q-gutter-sm q-mt-lg q-mb-sm">
      <q-btn unelevated color="primary" label="Новая таблица" @click="reset" />
      <q-btn outline color="primary" label="Загрузить пример" @click="loadSample" />
    </div>

    <pre class="data-grid-playground__json">{{ jsonPreview }}</pre>
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import {
  DataGrid,
  createEmptyDataGrid,
  parseDataGrid,
  type DataGridDocument
} from '@hp/data-grid'

const labels = {
  addColumn: 'Добавить колонку',
  addRow: 'Добавить строку',
  untitledColumn: 'Колонка',
  deleteColumn: 'Удалить колонку',
  deleteRow: 'Удалить строку'
}

const document = ref<DataGridDocument>(createEmptyDataGrid({
  columns: 2,
  rows: 1,
  columnTitle: labels.untitledColumn
}))

const jsonPreview = computed(() => JSON.stringify(document.value, null, 2))

function reset() {
  document.value = createEmptyDataGrid({
    columns: 1,
    rows: 1,
    columnTitle: labels.untitledColumn
  })
}

function loadSample() {
  document.value = parseDataGrid({
    version: 1,
    meta: { id: 'grid_sample', title: 'Пример', templateId: null },
    columns: [
      { id: 'col_name', title: 'Название', type: 'text' },
      { id: 'col_note', title: 'Заметка', type: 'text' }
    ],
    rows: [
      { id: 'row_1', cells: { col_name: { value: 'Молоко' }, col_note: { value: '2 л' } } },
      { id: 'row_2', cells: { col_name: { value: 'Хлеб' }, col_note: { value: '' } } }
    ]
  })
}
</script>

<style lang="scss" scoped>
.data-grid-playground__json {
  margin: 0;
  padding: 12px 14px;
  border-radius: 12px;
  background: #f6f7fb;
  border: 1px solid #e4e7ef;
  font-size: 12px;
  line-height: 1.45;
  overflow: auto;
  max-height: 360px;
}
</style>
