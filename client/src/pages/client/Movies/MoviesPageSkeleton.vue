<template>
  <div class="movies-grid">
    <MovieCardSkeleton
      v-for="index in 12"
      :key="index"
    />
  </div>
</template>

<script lang="ts" setup>
import MovieCardSkeleton from 'src/components/client/Movies/MovieCardSkeleton.vue'
</script>

<style lang="scss" scoped>
.movies-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 1.5rem 1.25rem;
}
</style>
