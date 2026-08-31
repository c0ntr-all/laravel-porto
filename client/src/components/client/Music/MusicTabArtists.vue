<template>
  <div class="row q-col-gutter-md q-mb-md">
    <div class="col-12 col-md-4 col-lg-3">
      <q-card class="artists-sidebar" flat>
        <q-card-section>
          <MusicTabArtistsFilter
            :tags-match="catalog.artistListTagsMatch"
            :tags-nested="catalog.artistListTagsNested"
            :selected-tags="catalog.artistListTags"
            @change="onFilterChange"
          />
        </q-card-section>
      </q-card>
    </div>
    <div class="col-12 col-md-8 col-lg-9">
      <q-card class="q-mb-md" flat>
        <q-card-section>
          <div class="row items-center q-col-gutter-sm">
            <div class="col">
              <q-input
                v-model="searchText"
                label="Search artists"
                outlined
                dense
                debounce="400"
                clearable
                @update:model-value="onSearch"
              >
                <template #prepend>
                  <q-icon name="search" />
                </template>
              </q-input>
            </div>
            <div class="col-auto">
              <q-btn-toggle
                v-model="cardMode"
                unelevated
                dense
                no-caps
                toggle-color="primary"
                color="grey-2"
                text-color="primary"
                :options="[
                  { value: 'card', slot: 'card' },
                  { value: 'row', slot: 'row' }
                ]"
              >
                <template #card>
                  <q-icon name="grid_view" size="sm" />
                </template>
                <template #row>
                  <q-icon name="view_agenda" size="sm" />
                </template>
              </q-btn-toggle>
            </div>
          </div>
        </q-card-section>
      </q-card>

      <q-card flat>
        <q-card-section>
          <MusicArtistsListSkeleton
            v-if="catalog.isArtistsLoading"
            :card-mode="cardMode"
          />
          <template v-else-if="catalog.artists.length">
            <MusicArtistsList
              :artists="catalog.artists"
              :card-mode="cardMode"
            />
            <div
              v-if="catalog.hasMoreArtists"
              ref="sentinel"
              class="artists-list-sentinel"
            />
            <div
              v-if="catalog.isArtistsLoadingMore"
              class="flex justify-center q-my-md"
            >
              <q-spinner color="primary" size="2em" />
            </div>
          </template>
          <AppNoResultsPlug
            v-else
            title="No artists found"
            body="Try another search or tag filter"
          />
        </q-card-section>
      </q-card>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { onMounted, ref } from 'vue'
import { useMusicCatalogStore } from 'src/stores/modules/musicCatalogStore'
import { useScrollSentinel } from 'src/composables/useScrollSentinel'
import MusicArtistsList from 'src/components/client/Music/MusicArtistsList.vue'
import MusicArtistsListSkeleton from 'src/components/client/Music/MusicArtistsListSkeleton.vue'
import MusicTabArtistsFilter from 'src/components/client/Music/MusicTabArtistsFilter.vue'
import AppNoResultsPlug from 'src/components/default/AppNoResultsPlug.vue'

const catalog = useMusicCatalogStore()
const cardMode = ref<'card' | 'row'>('card')
const searchText = ref(catalog.artistListName)

const onSearch = (value: string | number | null) => {
  void catalog.getArtists({ name: String(value ?? '') })
}

const onFilterChange = (payload: {
  tags: string[]
  tagsMatch: 'and' | 'or'
  tagsNested: boolean
}) => {
  void catalog.getArtists({
    tags: payload.tags,
    tagsMatch: payload.tagsMatch,
    tagsNested: payload.tagsNested
  })
}

const { sentinel } = useScrollSentinel(
  () => { void catalog.getArtists({ append: true }) },
  () => catalog.hasMoreArtists && !catalog.isArtistsLoading && !catalog.isArtistsLoadingMore
)

onMounted(() => {
  if (!catalog.artists.length) {
    void catalog.getArtists()
  }
})
</script>

<style lang="scss" scoped>
.artists-sidebar {
  @media (min-width: $breakpoint-md-min) {
    position: sticky;
    top: 16px;
  }
}

.artists-list-sentinel {
  width: 100%;
  height: 1px;
}
</style>
