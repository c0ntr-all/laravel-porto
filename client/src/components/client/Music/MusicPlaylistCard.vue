<template>
  <router-link :to="`/music/playlists/${playlist.id}`" class="playlist-card-link">
    <q-card class="playlist-card" flat bordered>
      <q-img
        v-if="playlist.image"
        :src="playlist.image"
        :alt="playlist.name"
        ratio="1"
      />
      <div v-else class="playlist-card__placeholder">
        <q-icon name="queue_music" size="64px" color="grey-5" />
      </div>
      <q-card-section>
        <div class="text-subtitle1 text-weight-medium ellipsis">{{ playlist.name }}</div>
        <div class="text-caption text-grey-7 q-mt-xs">
          {{ tracksLabel }}
        </div>
        <div
          v-if="playlist.description"
          class="text-caption text-grey-6 ellipsis-2-lines q-mt-xs"
        >
          {{ playlist.description }}
        </div>
      </q-card-section>
    </q-card>
  </router-link>
</template>

<script lang="ts" setup>
import { computed } from 'vue'
import { IPlaylist } from 'src/types'
import { formatPlaylistTracksCount } from 'src/utils/playlist'

const props = defineProps<{
  playlist: IPlaylist
}>()

const tracksLabel = computed(() => formatPlaylistTracksCount(props.playlist))
</script>

<style lang="scss" scoped>
.playlist-card-link {
  display: block;
  text-decoration: none;
  color: inherit;
}

.playlist-card {
  height: 100%;
  overflow: hidden;

  &__placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    aspect-ratio: 1;
    background: #f0f2f5;
  }
}

.ellipsis-2-lines {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
