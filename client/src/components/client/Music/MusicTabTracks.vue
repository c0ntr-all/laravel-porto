<template>
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
    </q-card-section>
  </q-card>

  <MusicTracksListSkeleton v-if="catalog.isTracksLoading" />

  <q-card v-else class="q-mb-md" flat>
    <q-card-section v-if="catalog.tracks.length" class="q-pa-lg">
      <div class="tracks-list q-gutter-xs q-pr-lg">
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
      body="Try another search"
    />
  </q-card>
</template>

<script lang="ts" setup>
import { onMounted, ref } from 'vue'
import { useMusicCatalogStore } from 'src/stores/modules/musicCatalogStore'
import { useMusicPlayer } from 'src/stores/modules/musicPlayer'
import { useScrollSentinel } from 'src/composables/useScrollSentinel'
import MusicTrackCard from 'src/components/client/Music/MusicTrackCard.vue'
import MusicTracksListSkeleton from 'src/components/client/Music/MusicTracksListSkeleton.vue'
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
.tracks-list {
  max-width: 700px;
  border-right: 1px solid #ccc;
}

.tracks-list-sentinel {
  width: 100%;
  height: 1px;
}
</style>
