<template>
  <q-card class="playlist-card col-4">
    <q-img :src="playlist.image" :alt="playlist.name">
      <div class="absolute-bottom text-h6">
        <router-link :to="`/music/playlists/${playlist.id}`" class="playlist-card__link">
          {{ playlist.name }}
        </router-link>
      </div>
    </q-img>
  </q-card>
</template>

<script lang="ts" setup>
import { IPlaylist } from 'src/components/client/Music/types'

const props = defineProps<{
  playlist: IPlaylist
}>()
const playlist = props.playlist
</script>

<style lang="scss" scoped>
.playlist-card {
  width: 100%;
  max-width: 250px;

  &__link {
    text-decoration: none;
    color: #fff;

    &:hover {
      color: #ccc;
    }
  }
}
</style>
