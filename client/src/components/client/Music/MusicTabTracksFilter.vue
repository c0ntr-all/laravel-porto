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
      <div class="tracks-rate-filter q-mb-md">
        <div class="tracks-rate-filter__label">Rating</div>
        <div class="tracks-rate-filter__row">
          <q-btn
            v-for="option in rateOptions"
            :key="option.value"
            class="tracks-rate-filter__chip"
            unelevated
            dense
            no-caps
            :color="isRateSelected(option.value) ? 'primary' : 'grey-2'"
            :text-color="isRateSelected(option.value) ? 'white' : 'grey-8'"
            :icon="option.icon || undefined"
            :label="option.label || undefined"
            @click="toggleRate(option.value)"
          >
            <q-tooltip>{{ option.hint }}</q-tooltip>
          </q-btn>
        </div>
      </div>
    </template>
  </MusicTabArtistsFilter>
</template>

<script lang="ts" setup>
import { useMusicCatalogStore } from 'src/stores/modules/musicCatalogStore'
import MusicTabArtistsFilter from 'src/components/client/Music/MusicTabArtistsFilter.vue'

const catalog = useMusicCatalogStore()

const rateOptions = [
  { value: 1, icon: 'sentiment_very_dissatisfied', hint: '1 star', label: '' },
  { value: 2, icon: 'sentiment_dissatisfied', hint: '2 stars', label: '' },
  { value: 3, icon: 'sentiment_satisfied', hint: '3 stars', label: '' },
  { value: 4, icon: 'sentiment_very_satisfied', hint: '4 stars', label: '' },
  { value: 0, icon: '', hint: 'Not rated yet', label: 'Unrated' }
] as const

const isRateSelected = (value: number): boolean => {
  return catalog.trackListRates.includes(value)
}

const toggleRate = (value: number): void => {
  const current = catalog.trackListRates
  const next = current.includes(value)
    ? current.filter(item => item !== value)
    : [...current, value]

  void catalog.getTracks({ rates: next })
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

<style lang="scss" scoped>
.tracks-rate-filter {
  &__label {
    margin-bottom: 8px;
    color: rgba(0, 0, 0, 0.7);
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 0.04em;
    text-transform: uppercase;
  }

  &__row {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
  }

  &__chip {
    min-width: 40px;
    padding: 0 8px;
  }
}
</style>
