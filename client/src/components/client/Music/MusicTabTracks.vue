<template>
  <div class="row q-col-gutter-md q-mb-md">
    <div class="col-12 col-md-4 col-lg-3">
      <q-card class="tracks-sidebar" flat>
        <q-card-section>
          <MusicTabTracksFilter />
        </q-card-section>
      </q-card>
    </div>
    <div class="col-12 col-md-8 col-lg-9">
      <q-card class="q-mb-md" flat>
        <q-card-section>
          <q-input
            v-model="searchText"
            label="Search tracks"
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
          <MusicTabTracksSort />
        </q-card-section>
      </q-card>

      <MusicTracksListSkeleton v-if="catalog.isTracksLoading" />

      <q-card v-else flat>
        <q-card-section v-if="catalog.tracks.length" class="q-pa-lg">
          <div class="tracks-list q-gutter-xs">
            <MusicTrackCard
              v-for="track in catalog.tracks"
              :key="track.id"
              :track="track"
              :actions="trackActions"
              @play="playTrack(track)"
            />
          </div>

          <div
            v-if="catalog.hasMoreTracks"
            ref="sentinel"
            class="tracks-list-sentinel"
          />
          <div
            v-if="catalog.isTracksLoadingMore"
            class="flex justify-center q-my-md"
          >
            <q-spinner color="primary" size="2em" />
          </div>
        </q-card-section>

        <AppNoResultsPlug
          v-else
          title="No tracks found"
          body="Try another search, rating or tag filter"
        />
      </q-card>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { onMounted, ref } from 'vue'
import { useMusicCatalogStore } from 'src/stores/modules/musicCatalogStore'
import { useMusicPlayer } from 'src/stores/modules/musicPlayer'
import { useScrollSentinel } from 'src/composables/useScrollSentinel'
import MusicTrackCard from 'src/components/client/Music/MusicTrackCard.vue'
import MusicTracksListSkeleton from 'src/components/client/Music/MusicTracksListSkeleton.vue'
import MusicTabTracksFilter from 'src/components/client/Music/MusicTabTracksFilter.vue'
import MusicTabTracksSort from 'src/components/client/Music/MusicTabTracksSort.vue'
import AppNoResultsPlug from 'src/components/default/AppNoResultsPlug.vue'
import { ITrack } from 'src/types'

const catalog = useMusicCatalogStore()
const musicPlayer = useMusicPlayer()
const trackActions = ['addToPlaylist']
const searchText = ref(catalog.trackListName)

const onSearch = (value: string | number | null) => {
  void catalog.getTracks({ name: String(value ?? '') })
}

const playTrack = (track: ITrack) => {
  musicPlayer.toggleTrack(track, catalog.tracks)
}

const { sentinel } = useScrollSentinel(
  () => { void catalog.getTracks({ append: true }) },
  () => catalog.hasMoreTracks && !catalog.isTracksLoading && !catalog.isTracksLoadingMore
)

onMounted(() => {
  if (!catalog.tracks.length) {
    void catalog.getTracks()
  }
})
</script>

<style lang="scss" scoped>
.tracks-sidebar {
  @media (min-width: $breakpoint-md-min) {
    position: sticky;
    top: 16px;
  }
}

.tracks-list-sentinel {
  width: 100%;
  height: 1px;
}
</style>
