<template>
  <div class="column q-gutter-md q-mb-lg">
    <AppArtistCardRowSkeleton v-for="n in 20" :key="n"/>
  </div>
</template>
<script lang="ts" setup>
import AppArtistCardRowSkeleton from 'src/components/client/Music/MusicArtistCardRowSkeleton.vue'
</script>
