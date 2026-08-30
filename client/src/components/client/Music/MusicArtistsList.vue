<template>
  <div v-if="cardMode === 'card'" class="artists-list row items-start q-gutter-md q-mb-lg">
    <MusicArtistCard
      v-for="artist in artists"
      :key="artist.id"
      :artist="artist"
    />
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
