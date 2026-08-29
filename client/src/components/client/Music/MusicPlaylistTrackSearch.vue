<template>
  <div class="playlist-search q-pa-lg">
    <q-input
      :model-value="playlistStore.searchQuery"
      type="search"
      outlined
      dense
      clearable
      placeholder="Search tracks to add"
      hint="At least 3 characters. Use Artist - Track to search by artist."
      @update:model-value="onQueryChange"
    >
      <template v-slot:prepend>
        <q-icon name="search"/>
      </template>
    </q-input>

    <div v-if="showResults" class="playlist-search__results q-mt-md">
      <q-linear-progress
        v-if="playlistStore.isSearching && playlistStore.searchResults.length"
        class="q-mb-sm"
        indeterminate
        color="primary"
      />

      <MusicTracksListSkeleton v-if="playlistStore.isSearching && !playlistStore.searchResults.length"/>

      <template v-else-if="playlistStore.searchResults.length">
        <div class="tracks-list q-gutter-xs">
          <MusicTrackCard
            v-for="track in playlistStore.searchResults"
            :key="track.id"
            :track="track"
            :actions="['addToThisPlaylist']"
            :playlist-id="playlistId"
            :in-playlist="playlistStore.isInPlaylist(track.id)"
            :adding="playlistStore.isAddingTrack(track.id)"
            @play="playTrack(track)"
            @add="playlistStore.addTrackToPlaylist"
          />
        </div>

        <div v-if="playlistStore.searchCursor" class="q-mt-md flex justify-center">
          <q-btn
            color="primary"
            outline
            :loading="playlistStore.isSearchingMore"
            @click="playlistStore.loadMoreSearchResults"
          >
            Load more
          </q-btn>
        </div>
      </template>

      <AppNoResultsPlug
        v-else
        title="No tracks found"
        body="Try another artist or track name"
      />
    </div>
  </div>
</template>

<script lang="ts" setup>
import { computed } from 'vue'
import {
  TRACK_SEARCH_MIN_LENGTH,
  useMusicPlaylistStore
} from 'src/stores/modules/musicPlaylistStore'
import { useMusicPlayer } from 'src/stores/modules/musicPlayer'
import MusicTrackCard from 'src/components/client/Music/MusicTrackCard.vue'
import MusicTracksListSkeleton from 'src/components/client/Music/MusicTracksListSkeleton.vue'
import AppNoResultsPlug from 'src/components/default/AppNoResultsPlug.vue'
import { ITrack } from 'src/types'

const playlistStore = useMusicPlaylistStore()
const musicPlayer = useMusicPlayer()

const playlistId = computed(() => playlistStore.playlist?.id ?? '0')
const showResults = computed(() => (
  playlistStore.searchQuery.trim().length >= TRACK_SEARCH_MIN_LENGTH
))

const onQueryChange = (value: string | number | null) => {
  playlistStore.setSearchQuery(String(value ?? ''))
}

const playTrack = (track: ITrack) => {
  musicPlayer.toggleTrack(track, playlistStore.searchResults)
}
</script>

<style lang="scss" scoped>
.playlist-search {
  max-width: 700px;
  border-bottom: 1px solid #ccc;
}

.tracks-list {
  max-width: 700px;
}
</style>
