<template>
  <div class="tracks-sort">
    <q-btn-toggle
      :model-value="catalog.trackListSortField"
      class="tracks-sort__fields"
      unelevated
      dense
      no-caps
      toggle-color="primary"
      color="grey-2"
      text-color="primary"
      :options="sortOptions"
      @update:model-value="onFieldChange"
    />
    <q-btn
      class="tracks-sort__direction"
      unelevated
      dense
      round
      color="grey-2"
      text-color="primary"
      :icon="catalog.trackListSortDesc ? 'south' : 'north'"
      @click="onToggleDirection"
    >
      <q-tooltip>{{ directionHint }}</q-tooltip>
    </q-btn>
  </div>
</template>

<script lang="ts" setup>
import { computed } from 'vue'
import { useMusicCatalogStore, type TrackSortField } from 'src/stores/modules/musicCatalogStore'

const catalog = useMusicCatalogStore()

const sortOptions: Array<{ label: string; value: TrackSortField }> = [
  { label: 'Newest', value: 'created_at' },
  { label: 'Name', value: 'name' },
  { label: 'Rating', value: 'rate' }
]

const defaultDesc = (field: TrackSortField): boolean => field !== 'name'

const directionHint = computed(() => {
  if (catalog.trackListSortField === 'name') {
    return catalog.trackListSortDesc ? 'Z to A' : 'A to Z'
  }

  if (catalog.trackListSortField === 'rate') {
    return catalog.trackListSortDesc ? 'Highest first' : 'Lowest first'
  }

  return catalog.trackListSortDesc ? 'Newest first' : 'Oldest first'
})

const onFieldChange = (value: string | number | null): void => {
  const field = (value as TrackSortField) || 'created_at'
  void catalog.getTracks({
    sortField: field,
    sortDesc: defaultDesc(field)
  })
}

const onToggleDirection = (): void => {
  void catalog.getTracks({
    sortDesc: !catalog.trackListSortDesc
  })
}
</script>

<style lang="scss" scoped>
.tracks-sort {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 6px;
  margin-top: 10px;

  &__fields {
    flex: 1 1 auto;
  }
}
</style>
