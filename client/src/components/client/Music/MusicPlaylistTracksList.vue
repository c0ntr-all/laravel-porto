<template>
  <div class="q-pa-lg">
    <div class="tracks-list q-gutter-xs q-pr-lg">
      <MusicTrackCard
        v-for="track in tracks"
        :key="track.id"
        :track="track"
        :actions="playlistActions"
        :playlistId="playlistId || '0'"
        @play="initPlay(track)"
        @remove="removeTrackFromList"
      />
    </div>
  </div>
</template>
<script lang="ts" setup>
import { ref } from 'vue'
import { useMusicPlayer } from 'src/stores/modules/musicPlayer'
import MusicTrackCard from 'src/components/client/Music/MusicTrackCard.vue'
import { ITrack } from 'src/components/client/Music/types'

const props = defineProps<{
  tracks: ITrack[],
  playlistId: string
}>()
const musicPlayer = useMusicPlayer()

const playlistActions = ['addToPlaylist', 'deleteTrackFromPlaylist']
const tracks = ref(props.tracks)

const initPlay = (track: ITrack) => {
  // Replacing playlist with new track
  if (!musicPlayer.playlist.includes(track)) {
    musicPlayer.setPlaylist(tracks.value)
  }
  musicPlayer.playTrack(track)
}
const removeTrackFromList = (trackId: string) => {
  const index = tracks.value.findIndex(item => item.id === trackId)
  if (index !== -1) {
    tracks.value.splice(index, 1)
  }
}
</script>
<style lang="scss" scoped>
.tracks-list {
  max-width: 700px;
  border-right: 1px solid #ccc;
}
</style>
