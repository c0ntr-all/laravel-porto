<template>
  <div class="q-pa-lg">
    <div class="tracks-list q-gutter-xs q-pr-lg">
      <MusicTrackCard
        v-for="track in tracks"
        :key="track.id"
        :track="track"
        :actions="albumActions"
        @play="initPlay(track)"
        @remove="removeTrackFromList"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useMusicPlayer } from 'src/stores/modules/musicPlayer'
import MusicTrackCard from 'src/components/client/Music/MusicTrackCard.vue'

interface Track {
  id: string
  name: string
  number: number
  artist: string
  image: string
  duration: string
  rate: number
}

interface Props {
  tracks?: {
    data: Track[]
  }
}

const props = withDefaults(defineProps<Props>(), {
  tracks: () => ({
    data: [] as Track[]
  })
})
const musicPlayer = useMusicPlayer()
const albumActions = ['addToPlaylist']
const tracks = ref(props.tracks.data)

const initPlay = (track: Track) => {
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
