<template>
  <MusicTracksListSkeleton v-if="catalog.isArtistTracksLoading"/>
  <q-card v-else class="q-mb-md" flat>
    <q-card-section v-if="catalog.artistTracks.length" class="q-pa-lg">
      <div class="tracks-list q-gutter-xs q-pr-lg">
        <MusicTrackCard
          v-for="track in catalog.artistTracks"
          :key="track.id"
          :track="track"
          :actions="trackActions"
          @play="playTrack(track)"
        />
      </div>

      <div v-if="catalog.artistTracksCursor" class="q-mt-md flex justify-center">
        <q-btn
          color="primary"
          outline
          :loading="catalog.isArtistTracksLoadingMore"
          @click="loadMore"
        >
          Load more
        </q-btn>
      </div>
    </q-card-section>

    <AppNoResultsPlug
      v-else
      title="No tracks yet"
      body="This artist has no tracks"
    />
  </q-card>
</template>

<script lang="ts" setup>
import { watch } from 'vue'
import { useMusicCatalogStore } from 'src/stores/modules/musicCatalogStore'
import { useMusicPlayer } from 'src/stores/modules/musicPlayer'
import MusicTrackCard from 'src/components/client/Music/MusicTrackCard.vue'
import MusicTracksListSkeleton from 'src/components/client/Music/MusicTracksListSkeleton.vue'
import AppNoResultsPlug from 'src/components/default/AppNoResultsPlug.vue'
import { ITrack } from 'src/types'

const props = defineProps<{
  artistId: string
  artistName?: string
}>()

const catalog = useMusicCatalogStore()
const musicPlayer = useMusicPlayer()
const trackActions = ['addToPlaylist']

const loadTracks = (append = false) => {
  return catalog.getArtistTracks(props.artistId, {
    append,
    fallbackArtist: props.artistName
  })
}

const loadMore = () => {
  return loadTracks(true)
}

const playTrack = (track: ITrack) => {
  musicPlayer.toggleTrack(track, catalog.artistTracks)
}

watch(() => props.artistId, () => {
  loadTracks()
}, { immediate: true })
</script>

<style lang="scss" scoped>
.tracks-list {
  max-width: 700px;
  border-right: 1px solid #ccc;
}
</style>
