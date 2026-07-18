<template>
  <q-table
    :title="tableTitleName"
    :rows="periods"
    :columns="columns"
    row-key="id"
    :pagination="{rowsPerPage: 0}"
    :no-results-label="'No data'"
    dense
    flat
  >
    <template v-slot:header="props">
      <q-tr :props="props">
        <q-th auto-width />
        <q-th v-for="col in props.cols" :key="col.name" :props="props">
          {{ col.label }}
        </q-th>
      </q-tr>
    </template>
    <template v-slot:body="props">
      <q-tr :props="props">
        <q-td auto-width>
          <q-btn
            v-if="props.row.description"
            size="sm"
            color="accent"
            round
            dense
            @click="props.expand = !props.expand"
            :icon="props.expand ? 'remove' : 'add'"
          />
        </q-td>
        <q-td key="title" :props="props">
          {{ props.row.title }}
        </q-td>
        <q-td key="color" :props="props">
          <span class="periods-list__color-col" :style="{backgroundColor: props.row.color }"></span>
        </q-td>
        <q-td key="color" :props="props">
          {{ props.row.start_date }}
        </q-td>
        <q-td key="color" :props="props">
          {{ props.row.end_date }}
        </q-td>
      </q-tr>
      <q-tr v-show="props.expand" :props="props">
        <q-td colspan="100%">
          <div class="text-left">{{ props.row.description }}</div>
        </q-td>
      </q-tr>
    </template>
  </q-table>
</template>

<script lang="ts" setup>
import { onMounted, computed } from 'vue'
import { storeToRefs } from 'pinia'
import { usePeriodStore } from 'src/stores/modules/periodStore'

const periodStore = usePeriodStore()
const { periods, periodsCount } = storeToRefs(periodStore)
const columns = [
  {
    name: 'title',
    required: true,
    label: 'Заголовок',
    align: 'left',
    field: 'title',
    sortable: true
  }, {
    name: 'color',
    align: 'center',
    label: 'Цвет',
    field: 'color',
    sortable: true
  }, {
    name: 'start_date',
    align: 'center',
    label: 'Дата начала',
    field: 'start_date',
    sortable: true
  }, {
    name: 'end_date',
    align: 'center',
    label: 'Дата окончания',
    field: 'end_date',
    sortable: true
  }
]
const tableTitleName = computed(() => `Periods (${periodsCount.value})`)
onMounted(() => {
  periodStore.getPeriods()
})

</script>

<style lang="scss" scoped>
.periods-list__color-col {
  display: block;
  width: 1rem;
  height: 1rem;
  margin: auto;
}
</style>
