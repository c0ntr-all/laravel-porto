<template>
  <div class="q-pa-lg">
    <div class="tracks-list q-gutter-xs q-pr-lg">
      <MusicTrackCard
        v-for="track in tracks"
        :key="track.id"
        :track="track"
        :actions="playlistActions"
        :playlist-id="playlistId"
        @play="initPlay(track)"
        @remove="playlistStore.removeTrackLocal"
      />
    </div>
  </div>
</template>
<script lang="ts" setup>
import { computed } from 'vue'
import { useMusicPlayer } from 'src/stores/modules/musicPlayer'
import { useMusicPlaylistStore } from 'src/stores/modules/musicPlaylistStore'
import MusicTrackCard from 'src/components/client/Music/MusicTrackCard.vue'
import { ITrack } from 'src/types'

const playlistStore = useMusicPlaylistStore()
const musicPlayer = useMusicPlayer()

const playlistActions = ['addToPlaylist', 'deleteTrackFromPlaylist']
const tracks = computed(() => playlistStore.playlist?.tracks ?? [])
const playlistId = computed(() => playlistStore.playlist?.id ?? '0')

const initPlay = (track: ITrack) => {
  musicPlayer.toggleTrack(track, tracks.value)
}
</script>
<style lang="scss" scoped>
.tracks-list {
  max-width: 700px;
  border-right: 1px solid #ccc;
}
</style>
