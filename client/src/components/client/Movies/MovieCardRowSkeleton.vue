<template>
  <div class="movie-card-row-skeleton">
    <q-skeleton class="movie-card-row-skeleton__poster" square />
    <div class="movie-card-row-skeleton__body">
      <q-skeleton type="text" width="40%" />
      <q-skeleton type="text" width="25%" />
      <q-skeleton type="text" width="80%" />
    </div>
  </div>
</template>

<script lang="ts" setup>
</script>

<style lang="scss" scoped>
.movie-card-row-skeleton {
  display: flex;
  gap: 1rem;
  min-width: 0;
  padding: 12px;

  &__poster {
    flex: 0 0 92px;
    width: 92px;
    aspect-ratio: 2 / 3;
    border-radius: 12px;
  }

  &__body {
    flex: 1;
    min-width: 0;
  }
}
</style>
