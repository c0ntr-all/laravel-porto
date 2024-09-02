<template>
  <q-card class="q-mb-md" flat>
    <q-tabs
      v-model="tab"
      align="left"
      no-caps
      outside-arrows
      mobile-arrows
    >
      <q-tab name="tracks" label="Tracks"/>
      <q-tab name="artists" label="Artists"/>
      <q-tab name="playlists" label="Playlists"/>
      <q-tab name="genres" label="Genres"/>
      <q-tab name="history" label="History"/>
    </q-tabs>
  </q-card>
  <q-tab-panels
    v-model="tab"
    animated
    swipeable
    vertical
    transition-prev="jump-up"
    transition-next="jump-up"
  >
    <q-tab-panel name="tracks" class="q-pa-none">
<!--      <MusicTabTracks/>-->
    </q-tab-panel>

    <q-tab-panel name="artists" class="q-pa-none">
      <MusicTabArtists/>
    </q-tab-panel>

    <q-tab-panel name="playlists" class="q-pa-none">
      <MusicTabPlaylists/>
    </q-tab-panel>

    <q-tab-panel name="genres" class="q-pa-none">
      <!--      <TagsTab/>-->
      Теги
    </q-tab-panel>

    <q-tab-panel name="history" class="q-pa-none">
      <!--      <HistoryTab/>-->
      История
    </q-tab-panel>
  </q-tab-panels>
</template>
<script lang="ts" setup>
import { ref } from 'vue'

// import TracksTab from 'src/components/client/music/default/tracksTab/TracksTab.vue'
import MusicTabArtists from 'src/components/client/Music/MusicTabArtists.vue'
import MusicTabPlaylists from 'src/components/client/Music/MusicTabPlaylists.vue'
// import MusicTabTracks from 'src/components/client/Music/MusicTabTracks.vue'
// import TagsTab from 'src/components/client/music/music/tabs/tags/TagsTab.vue'
// import HistoryTab from 'src/components/client/music/music/tabs/HistoryTab.vue'

const tab = ref('artists')
</script>

<style lang="scss" scoped>
.tags-list {
  position: relative;
}

.q-tab-panels {
  background-color: transparent;
}
</style>
