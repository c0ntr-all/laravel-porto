<template>
  <div v-if="viewMode === MoviesViewModeEnum.TILE" class="movies-grid">
    <MovieCardSkeleton
      v-for="index in 12"
      :key="index"
    />
  </div>
  <div v-else class="movies-list">
    <MovieCardRowSkeleton
      v-for="index in 8"
      :key="index"
    />
  </div>
</template>

<script lang="ts" setup>
import { MoviesViewModeEnum } from 'src/enums/Movie/MoviesViewModeEnum'
import MovieCardSkeleton from 'src/components/client/Movies/MovieCardSkeleton.vue'
import MovieCardRowSkeleton from 'src/components/client/Movies/MovieCardRowSkeleton.vue'

withDefaults(defineProps<{
  viewMode?: MoviesViewModeEnum
}>(), {
  viewMode: MoviesViewModeEnum.TILE
})
</script>

<style lang="scss" scoped>
.movies-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 1.5rem 1.25rem;
}

.movies-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}
</style>
