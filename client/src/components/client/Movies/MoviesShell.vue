<template>
  <div class="movies-shell">
    <MovieFoldersSidebar class="movies-shell__sidebar" />
    <div class="movies-shell__content">
      <slot />
    </div>
  </div>
</template>

<script lang="ts" setup>
import MovieFoldersSidebar from 'src/components/client/Movies/MovieFoldersSidebar.vue'
</script>

<style lang="scss" scoped>
.movies-shell {
  display: grid;
  grid-template-columns: minmax(220px, 260px) minmax(0, 1fr);
  gap: 1.5rem;
  align-items: start;

  &__sidebar {
    position: sticky;
    top: 12px;
  }
}

@media (max-width: 960px) {
  .movies-shell {
    grid-template-columns: 1fr;

    &__sidebar {
      position: static;
    }
  }
}
</style>
