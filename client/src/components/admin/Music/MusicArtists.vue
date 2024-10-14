<template>
  <div class="text-h5 q-mb-md">Artists</div>
  <q-splitter v-model="artistsSplitter">
    <template v-slot:before>
      <q-tabs
        v-model="tabs"
        vertical
        class="text-teal"
      >
        <q-tab name="edit" icon="edit_square" label="Edit"/>
        <q-tab name="upload" icon="upload" label="Upload"/>
      </q-tabs>
    </template>

    <template v-slot:after>
      <q-tab-panels
        v-model="tabs"
        animated
        transition-prev="slide-down"
        transition-next="slide-up"
      >
        <q-tab-panel name="edit">
          <MusicArtistsEdit/>
        </q-tab-panel>

        <q-tab-panel name="upload">
          <MusicArtistsUpload/>
        </q-tab-panel>
      </q-tab-panels>
    </template>
  </q-splitter>
</template>

<script lang="ts" setup>
import { ref } from 'vue'
import MusicArtistsEdit from 'src/components/admin/Music/MusicArtistsEdit.vue'
import MusicArtistsUpload from 'src/components/admin/Music/MusicArtistsUpload.vue'

const artistsSplitter = ref(6)
const tabs = ref('edit')
</script>

<style scoped>

</style>
