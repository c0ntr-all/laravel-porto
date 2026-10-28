<template>
  <router-link
    :to="`/music/playlists/${playlist.id}`"
    class="playlist-row"
  >
    <div class="playlist-row__cover">
      <q-img
        v-if="playlist.image"
        :src="playlist.image"
        :alt="playlist.name"
        ratio="1"
        class="playlist-row__image"
      />
      <div v-else class="playlist-row__placeholder">
        <q-icon name="queue_music" size="20px" color="grey-5" />
      </div>
    </div>

    <div class="playlist-row__name" :title="playlist.name">
      {{ playlist.name }}
    </div>

    <div class="playlist-row__count">
      {{ tracksLabel }}
    </div>
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
.playlist-row {
  display: grid;
  grid-template-columns: 40px minmax(0, 1fr) auto;
  align-items: center;
  gap: 12px;
  min-height: 52px;
  padding: 8px 4px;
  color: inherit;
  text-decoration: none;
  border-bottom: 1px solid rgba(0, 0, 0, 0.08);
  transition: background-color 0.12s ease;

  &:hover {
    background: rgba(0, 0, 0, 0.03);

    .playlist-row__name {
      color: $primary;
    }
  }

  &:last-child {
    border-bottom: none;
  }

  &__cover {
    width: 40px;
    height: 40px;
    overflow: hidden;
    border-radius: 4px;
    background: #f0f2f5;
  }

  &__image {
    width: 100%;
    height: 100%;
  }

  &__placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
  }

  &__name {
    min-width: 0;
    font-size: 0.95rem;
    font-weight: 500;
    line-height: 1.3;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  &__count {
    color: #757575;
    font-size: 0.8rem;
    font-variant-numeric: tabular-nums;
    white-space: nowrap;
  }
}
</style>
