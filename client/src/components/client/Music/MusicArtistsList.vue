<template>
  <div v-if="cardMode === 'card'" class="row q-col-gutter-md q-mb-lg">
    <div
      v-for="artist in artists"
      :key="artist.id"
      class="col-6 col-sm-4 col-lg-3"
    >
      <MusicArtistCard :artist="artist" />
    </div>
  </div>
  <div v-else class="column q-gutter-md q-mb-lg">
    <MusicArtistCardRow
      v-for="artist in artists"
      :key="artist.id"
      :artist="artist"
    />
  </div>
</template>
<script lang="ts" setup>
import MusicArtistCard from 'src/components/client/Music/MusicArtistCard.vue'
import MusicArtistCardRow from 'src/components/client/Music/MusicArtistCardRow.vue'
import { IArtist } from 'src/types'

defineProps<{
  artists: IArtist[]
  cardMode: 'card' | 'row'
}>()
</script>
