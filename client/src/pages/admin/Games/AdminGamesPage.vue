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
    <q-tab name="import" label="Import"/>
  </q-tabs>

  <q-separator/>

  <q-tab-panels v-model="tabs">
    <q-tab-panel name="catalog">
      <GameManagerCatalog/>
    </q-tab-panel>
    <q-tab-panel name="import">
      <GameManagerImport/>
    </q-tab-panel>
  </q-tab-panels>
</template>

<script lang="ts" setup>
import { ref } from 'vue'
import GameManagerCatalog from 'src/components/admin/Games/GameManagerCatalog.vue'
import GameManagerImport from 'src/components/admin/Games/GameManagerImport.vue'

const tabs = ref('catalog')
</script>
