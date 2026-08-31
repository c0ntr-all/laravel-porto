<template>
  <div v-if="cardMode === 'card'" class="row q-col-gutter-md q-mb-lg">
    <div
      v-for="n in 8"
      :key="n"
      class="col-6 col-sm-4 col-lg-3"
    >
      <q-card flat bordered>
        <q-skeleton height="180px" square />
        <q-card-section class="q-pa-sm">
          <q-skeleton type="text" width="70%" />
        </q-card-section>
      </q-card>
    </div>
  </div>
  <div v-else class="column q-gutter-md q-mb-lg">
    <AppArtistCardRowSkeleton v-for="n in 8" :key="n" />
  </div>
</template>
<script lang="ts" setup>
import AppArtistCardRowSkeleton from 'src/components/client/Music/MusicArtistCardRowSkeleton.vue'

withDefaults(defineProps<{
  cardMode?: 'card' | 'row'
}>(), {
  cardMode: 'card'
})
</script>
