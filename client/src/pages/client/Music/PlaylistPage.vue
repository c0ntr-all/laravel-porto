<template>
  <div class="q-mb-sm">
    <q-btn
      icon="arrow_back"
      color="primary"
      :to="'/music'"
    >
      <div class="q-ml-xs">Back to the Music</div>
    </q-btn>
  </div>
  <PlaylistPageSkeleton v-if="playlistStore.isPlaylistLoading && !playlistStore.playlist"/>
  <template v-else-if="playlistStore.playlist">
    <div class="playlist">
      <div class="playlist-head q-mb-lg">
        <div class="playlist-head__left">
          <div class="playlist-head__image q-mb-md">
            <img :src="playlistStore.playlist.image" alt=""/>
          </div>
        </div>
        <div class="playlist-head__right">
          <h2 class="playlist-head__name">{{ playlistStore.playlist.name }}</h2>
          <div class="playlist-head__description">
            <div class="playlist-head__description-item">
              {{ playlistStore.playlist.description }}
            </div>
          </div>
        </div>
      </div>
      <div class="playlist-body">
        <MusicPlaylistTrackSearch/>
        <MusicPlaylistTracksList v-if="playlistStore.playlist.tracks.length"/>
        <AppNoResultsPlug
          v-else
          title="Playlist is empty"
          body="Search for a track above to add it"
        />
      </div>
    </div>
  </template>
  <AppNoResultsPlug
    v-else
    title="Playlist not found"
    body="This playlist does not exist or is unavailable"
  />
</template>
<script lang="ts" setup>
import { watch } from 'vue'
import { useMusicPlaylistStore } from 'src/stores/modules/musicPlaylistStore'
import MusicPlaylistTracksList from 'src/components/client/Music/MusicPlaylistTracksList.vue'
import MusicPlaylistTrackSearch from 'src/components/client/Music/MusicPlaylistTrackSearch.vue'
import PlaylistPageSkeleton from 'src/pages/client/Music/PlaylistPageSkeleton.vue'
import AppNoResultsPlug from 'src/components/default/AppNoResultsPlug.vue'

const props = defineProps<{
  id: string
}>()

const playlistStore = useMusicPlaylistStore()

watch(() => props.id, (id) => {
  void playlistStore.getPlaylist(id)
}, { immediate: true })
</script>

<style lang="scss" scoped>
.playlist-head {
  display: flex;
  column-gap: 1rem;
  padding: 1rem 0 0 0;

  &__image {
    & > * {
      width: 200px;
      height: 200px;
    }
  }

  &__name {
    margin: 0 0 1rem 0;
    font-size: 45px;
    line-height: 45px;
    font-weight: 700;
  }

  &__description {
    margin: 0 0 1rem 0;

    &-item {
      margin-bottom: 1rem;
    }
  }
}

.playlist-body {
  background: #fff;
}
</style>
