<template>
  <div class="q-mb-sm">
    <q-btn
      icon="arrow_back"
      color="primary"
      :to="{ name: 'music-playlists' }"
    >
      <div class="q-ml-xs">Back to the Music</div>
    </q-btn>
  </div>
  <PlaylistPageSkeleton v-if="playlistStore.isPlaylistLoading && !playlistStore.playlist"/>
  <template v-else-if="playlistStore.playlist">
    <div class="playlist">
      <MusicPlaylistHeader :playlist="playlistStore.playlist" />
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
import MusicPlaylistHeader from 'src/components/client/Music/MusicPlaylistHeader.vue'
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
.playlist-body {
  background: #fff;
}
</style>
