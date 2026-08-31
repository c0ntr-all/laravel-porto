<template>
  <div class="album-head q-mb-lg">
    <q-skeleton class="album-head__cover" square />
    <div class="album-head__info">
      <q-skeleton type="text" width="240px" class="text-h4" />
      <q-skeleton type="text" width="70%" />
      <q-skeleton type="text" width="40%" />
      <q-skeleton type="QBtn" width="120px" />
    </div>
  </div>

  <div class="album-body">
    <div class="media-grid">
      <GalleryMediaCardSkeleton
        v-for="index in 12"
        :key="index"
      />
    </div>
  </div>
</template>

<script lang="ts" setup>
import GalleryMediaCardSkeleton from 'src/components/client/Gallery/GalleryMediaCardSkeleton.vue'
</script>

<style lang="scss" scoped>
.album-head {
  display: flex;
  gap: 1.5rem;
  align-items: flex-start;

  &__cover {
    width: 220px;
    height: 220px;
    border-radius: 18px;
  }

  &__info {
    flex: 1;
    padding-top: 0.5rem;
  }
}

.album-body {
  padding: 1rem;
  border-radius: 16px;
  background: #fff;
}

.media-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(168px, 1fr));
  gap: 8px;
}
</style>
