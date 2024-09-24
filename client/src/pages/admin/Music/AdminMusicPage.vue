<template>
  <q-tabs
    v-model="tabs"
    dense
    class="text-grey"
    active-color="primary"
    indicator-color="primary"
    align="left"
    narrow-indicator
  >
    <q-tab name="artists" label="Artists"/>
    <q-tab name="albums" label="Albums"/>
    <q-tab name="tracks" label="Tracks"/>
    <q-tab name="tags" label="Tags"/>
  </q-tabs>

  <q-separator/>

  <q-tab-panels v-model="tabs">
    <q-tab-panel name="artists">
      <AdminMusicTabArtists/>
    </q-tab-panel>
    <q-tab-panel name="albums">
      <AdminMusicTabAlbums/>
    </q-tab-panel>
    <q-tab-panel name="tracks">
      <AdminMusicTabTracks/>
    </q-tab-panel>
    <q-tab-panel name="tags">
      <AdminMusicTabTags/>
    </q-tab-panel>
  </q-tab-panels>
</template>
<script lang="ts" setup>
import { ref } from 'vue'
import AdminMusicTabArtists from 'src/components/admin/Music/MusicArtists.vue'
import AdminMusicTabAlbums from 'src/components/admin/Music/MusicAlbums.vue'
import AdminMusicTabTracks from 'src/components/admin/Music/MusicTracks.vue'
import AdminMusicTabTags from 'src/components/admin/Music/MusicTags.vue'

const tabs = ref('artists')
</script>
