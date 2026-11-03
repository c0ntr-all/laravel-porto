<template>
  <MusicTabArtistsFilter
    :tags-match="catalog.trackListTagsMatch"
    :tags-nested="catalog.trackListTagsNested"
    :selected-tags="catalog.trackListTags"
    :extra-dirty="catalog.trackListRates.length > 0"
    @change="onTagsChange"
    @reset="onReset"
  >
    <template #before-tags>
      <MusicTrackRateFilter
        class="q-mb-md"
        :model-value="catalog.trackListRates"
        @update:model-value="onRatesChange"
      />
    </template>
  </MusicTabArtistsFilter>
</template>

<script lang="ts" setup>
import { useMusicCatalogStore } from 'src/stores/modules/musicCatalogStore'
import MusicTabArtistsFilter from 'src/components/client/Music/MusicTabArtistsFilter.vue'
import MusicTrackRateFilter from 'src/components/client/Music/MusicTrackRateFilter.vue'

const catalog = useMusicCatalogStore()

const onRatesChange = (rates: number[]): void => {
  void catalog.getTracks({ rates })
}

const onTagsChange = (payload: {
  tags: string[]
  tagsMatch: 'and' | 'or'
  tagsNested: boolean
}): void => {
  void catalog.getTracks({
    tags: payload.tags,
    tagsMatch: payload.tagsMatch,
    tagsNested: payload.tagsNested
  })
}

const onReset = (): void => {
  void catalog.getTracks({
    rates: [],
    tags: [],
    tagsMatch: 'or',
    tagsNested: true
  })
}
</script>
