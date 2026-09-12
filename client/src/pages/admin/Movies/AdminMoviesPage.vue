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
    <q-tab name="catalog" label="Catalog"/>
    <q-tab name="genres" label="Genres"/>
  </q-tabs>

  <q-separator/>

  <q-tab-panels v-model="tabs">
    <q-tab-panel name="catalog">
      <MovieManagerCatalog/>
    </q-tab-panel>
    <q-tab-panel name="genres">
      <MovieManagerGenres/>
    </q-tab-panel>
  </q-tab-panels>
</template>

<script lang="ts" setup>
import { ref } from 'vue'
import MovieManagerCatalog from 'src/components/admin/Movies/MovieManagerCatalog.vue'
import MovieManagerGenres from 'src/components/admin/Movies/MovieManagerGenres.vue'

const tabs = ref('catalog')
</script>
