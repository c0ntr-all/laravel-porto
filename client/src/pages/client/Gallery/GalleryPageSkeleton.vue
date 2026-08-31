<template>
  <div class="gallery-grid">
    <GalleryAlbumCardSkeleton
      v-for="index in 8"
      :key="index"
    />
  </div>
</template>

<script lang="ts" setup>
import GalleryAlbumCardSkeleton from 'src/components/client/Gallery/GalleryAlbumCardSkeleton.vue'
</script>

<style lang="scss" scoped>
.gallery-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 1.5rem 1.25rem;
}
</style>
