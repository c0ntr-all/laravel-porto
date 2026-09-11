<template>
  <div class="movie-card-skeleton">
    <q-skeleton class="movie-card-skeleton__poster" square />
    <q-skeleton type="text" width="80%" />
    <q-skeleton type="text" width="45%" />
  </div>
</template>

<script lang="ts" setup>
</script>

<style lang="scss" scoped>
.movie-card-skeleton {
  min-width: 0;

  &__poster {
    width: 100%;
    aspect-ratio: 2 / 3;
    border-radius: 16px;
    margin-bottom: 0.75rem;
  }
}
</style>
