<template>
  <div>
    <q-tabs
      v-model="tabs"
      dense
      align="left"
      active-color="primary"
      indicator-color="primary"
      class="q-mb-md"
    >
      <q-tab name="edit" icon="library_music" label="Catalog" />
      <q-tab name="upload" icon="upload" label="Upload" />
    </q-tabs>
    <q-separator class="q-mb-md" />
    <q-tab-panels v-model="tabs" animated>
      <q-tab-panel name="edit" class="q-pa-none">
        <MusicArtistsEdit />
      </q-tab-panel>
      <q-tab-panel name="upload" class="q-pa-none">
        <MusicArtistsUpload />
      </q-tab-panel>
    </q-tab-panels>
  </div>
</template>

<script lang="ts" setup>
import { ref } from 'vue'
import MusicArtistsEdit from 'src/components/admin/Music/MusicArtistsEdit.vue'
import MusicArtistsUpload from 'src/components/admin/Music/MusicArtistsUpload.vue'

const tabs = ref('edit')
</script>
